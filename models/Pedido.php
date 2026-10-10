<?php

class Pedido
{
    private $conn;
    private $ultimoError = '';

    private function tablaExiste(string $tabla): bool
    {
        $stmt = $this->conn->query("SHOW TABLES LIKE '" . str_replace("'", "''", $tabla) . "'");
        if (!$stmt) {
            return false;
        }

        return $stmt->num_rows > 0;
    }

    private function columnasDisponibles(string $tabla, array $columnas): array
    {
        if ($tabla === '' || empty($columnas)) {
            return [];
        }

        $stmt = $this->conn->query(
            "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '" . str_replace("'", "''", $tabla) . "'"
        );

        if (!$stmt) {
            return [];
        }

        $existentes = [];
        while ($fila = $stmt->fetch_assoc()) {
            $existentes[] = $fila['COLUMN_NAME'];
        }

        return array_values(array_intersect($columnas, $existentes));
    }

    private function prepararSelectPedido(string $baseSelect, bool $conTipo = false): string
    {
        $select = $baseSelect;
        if ($conTipo && $this->tablaExiste('pedido_encargos')) {
            $select .= ", CASE WHEN pe.id IS NOT NULL THEN 'encargo' ELSE 'normal' END AS tipo";
        } else {
            $select .= ", 'normal' AS tipo";
        }

        return $select;
    }

    /* CONSTRUCTOR */
    public function __construct($conn)
    {
        $this->conn = $conn;
    }


    /* CREAR PEDIDO */
    public function crearPedido(
        $usuarioId,
        $fechaRecepcion,
        $franjaHoraria,
        $direccionEntrega,
        $metodoPago,
        $total
    ) {

        $sql = "
            INSERT INTO pedidos
            (
                usuario_id,
                fecha_recepcion,
                franja_horaria,
                direccion_entrega,
                metodo_pago,
                estado,
                total
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                'pendiente',
                ?
            )
        ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {

            error_log(
                'Error preparando crearPedido: '
                . $this->conn->error
            );

            return false;
        }

        $stmt->bind_param(
            "issssd",
            $usuarioId,
            $fechaRecepcion,
            $franjaHoraria,
            $direccionEntrega,
            $metodoPago,
            $total
        );

        if (!$stmt->execute()) {

            error_log(
                'Error ejecutando crearPedido: '
                . $stmt->error
            );

            $stmt->close();

            return false;
        }

        $pedidoId = $this->conn->insert_id;

        $stmt->close();

        return $pedidoId;
    }

    public function crearPedidoConDetalles(
        $usuarioId,
        $fechaRecepcion,
        $franjaHoraria,
        $direccionEntrega,
        $metodoPago,
        $detalles
    ) {
        if (empty($detalles)) {
            $this->ultimoError = 'El carrito está vacío.';
            return false;
        }

        $ids = array_map(
            static fn ($detalle) => (int) ($detalle['producto_id'] ?? 0),
            $detalles
        );
        $ids = array_values(array_unique($ids));
        if (in_array(0, $ids, true) || !$this->conn->begin_transaction()) {
            $this->ultimoError = 'Los productos del carrito no son válidos.';
            return false;
        }

        try {
            $marcadores = implode(',', array_fill(0, count($ids), '?'));
            $tipos = str_repeat('i', count($ids));
            $stmt = $this->conn->prepare(
                "SELECT id, nombre, precio, stock, unidad_venta, presentaciones, stocks_modalidades, activo FROM productos WHERE id IN ($marcadores) FOR UPDATE"
            );

            if (!$stmt) {
                throw new RuntimeException('No se pudieron consultar los productos.');
            }

            $stmt->bind_param($tipos, ...$ids);
            if (!$stmt->execute()) {
                $stmt->close();
                throw new RuntimeException('No se pudieron consultar los productos.');
            }

            $productos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            $porId = [];
            foreach ($productos as $producto) {
                $porId[(int) $producto['id']] = $producto;
            }

            if (count($porId) !== count($ids)) {
                throw new RuntimeException('Uno de los productos ya no existe.');
            }

            $detallesRevalidados = [];
            $stockSolicitado = [];
            $stocksActualizados = [];
            $total = 0;
            foreach ($detalles as $detalle) {
                $productoId = (int) ($detalle['producto_id'] ?? 0);
                $producto = $porId[$productoId] ?? null;

                if (!$producto || (int) $producto['activo'] !== 1) {
                    throw new RuntimeException('El producto seleccionado ya no está disponible.');
                }
                $unidadVenta = (string) ($detalle['unidad_venta'] ?? $producto['unidad_venta']);
                $presentaciones = Producto::obtenerPresentaciones($producto);
                if (!array_key_exists($unidadVenta, $presentaciones)) {
                    throw new RuntimeException('La presentación seleccionada ya no está disponible para: ' . $producto['nombre']);
                }

                $cantidad = Producto::normalizarCantidad($detalle['cantidad'] ?? null, $unidadVenta);
                if ($cantidad === false) {
                    throw new RuntimeException('La cantidad solicitada no es válida para: ' . $producto['nombre']);
                }

                $precio = $presentaciones[$unidadVenta];
                $consumo = Producto::consumoInventario($unidadVenta, $cantidad);

                $detallesRevalidados[] = [
                    'producto_id' => $productoId,
                    'cantidad' => $cantidad,
                    'unidad_venta' => $unidadVenta,
                    'precio_unitario' => $precio,
                    'base_stock' => $consumo['base'],
                    'consumo_stock' => $consumo['cantidad']
                ];
                $claveStock = $productoId . '|' . $consumo['base'];
                $stockSolicitado[$claveStock] = ($stockSolicitado[$claveStock] ?? 0) + $consumo['cantidad'];
                $total += $precio * $cantidad;
            }

            foreach ($stockSolicitado as $claveStock => $cantidadSolicitada) {
                [$productoId, $baseStock] = explode('|', $claveStock, 2);
                $productoId = (int) $productoId;
                $stocksPorModalidad = Producto::obtenerStocks($porId[$productoId]);
                if ($cantidadSolicitada > (float) ($stocksPorModalidad[$baseStock] ?? 0) + 0.000001) {
                    throw new RuntimeException('Stock insuficiente de ' . $baseStock . ' para: ' . $porId[$productoId]['nombre']);
                }
            }

            if ($total <= 0) {
                throw new RuntimeException('El total del pedido no es válido.');
            }

            $pedidoId = $this->crearPedido(
                $usuarioId,
                $fechaRecepcion,
                $franjaHoraria,
                $direccionEntrega,
                $metodoPago,
                $total
            );

            if (!$pedidoId) {
                throw new RuntimeException('No se pudo crear el pedido.');
            }

            foreach ($detallesRevalidados as $detalle) {
                $subtotal = $detalle['precio_unitario'] * $detalle['cantidad'];

                if (!$this->agregarDetalle(
                    $pedidoId,
                    $detalle['producto_id'],
                    $detalle['cantidad'],
                    $detalle['unidad_venta'],
                    $detalle['precio_unitario'],
                    $subtotal
                )) {
                    throw new RuntimeException('No se pudo guardar un detalle.');
                }

                $productoId = (int) $detalle['producto_id'];
                $baseStock = $detalle['base_stock'];
                $stocksActualizados[$productoId] ??= Producto::obtenerStocks($porId[$productoId]);
                $stocksActualizados[$productoId][$baseStock] = round(
                    $stocksActualizados[$productoId][$baseStock] - $detalle['consumo_stock'],
                    $baseStock === 'kilogramo' ? 1 : 0
                );
                $stockTotal = Producto::stockCompatibilidad($stocksActualizados[$productoId]);
                $stocksJson = json_encode($stocksActualizados[$productoId]);
                $stmtStock = $this->conn->prepare(
                    'UPDATE productos SET stocks_modalidades = ?, stock = ? WHERE id = ?'
                );
                if (!$stmtStock) {
                    throw new RuntimeException('No se pudo descontar el stock.');
                }
                $stmtStock->bind_param(
                    'sii',
                    $stocksJson,
                    $stockTotal,
                    $productoId
                );
                if (!$stmtStock->execute() || $stmtStock->affected_rows !== 1) {
                    $stmtStock->close();
                    throw new RuntimeException('No se pudo descontar el stock de ' . $baseStock . '.');
                }
                $stmtStock->close();
            }

            $this->conn->commit();

            return $pedidoId;
        } catch (Throwable $e) {
            $this->conn->rollback();
            $this->ultimoError = $e->getMessage();
            error_log('Error creando pedido transaccional: ' . $e->getMessage());

            return false;
        }
    }

    public function obtenerUltimoError()
    {
        return $this->ultimoError;
    }

    public function obtenerProductosActivos($ids)
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        if (empty($ids) || in_array(0, $ids, true)) {
            return [];
        }

        $marcadores = implode(',', array_fill(0, count($ids), '?'));
        $tipos = str_repeat('i', count($ids));
        $sql = "SELECT id, precio, unidad_venta, presentaciones, stocks_modalidades, stock FROM productos WHERE activo = 1 AND id IN ($marcadores)";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param($tipos, ...$ids);

        if (!$stmt->execute()) {
            $stmt->close();
            return [];
        }

        $productos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $porId = [];
        foreach ($productos as $producto) {
            $porId[(int) $producto['id']] = $producto;
        }

        return $porId;
    }


    /* CONTAR PEDIDOS PENDIENTES */
    public function cantidadPendientes($usuarioId)
    {

        $sql = "
            SELECT
                COUNT(*) AS cantidad

            FROM pedidos

            WHERE usuario_id = ?

            AND estado = 'pendiente'
        ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param(
            "i",
            $usuarioId
        );

        if (!$stmt->execute()) {

            $stmt->close();

            return 0;
        }

        $resultado = $stmt->get_result();

        $fila = $resultado->fetch_assoc();

        $stmt->close();

        return (int) $fila['cantidad'];
    }


    /* AGREGAR DETALLE */
    public function agregarDetalle(
        $pedidoId,
        $productoId,
        $cantidad,
        $unidadVenta,
        $precioUnitario,
        $subtotal
    ) {

        $sql = "
            INSERT INTO pedido_detalles
            (
                pedido_id,
                producto_id,
                cantidad,
                unidad_venta,
                precio_unitario,
                subtotal
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
            )
        ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {

            error_log(
                'Error preparando agregarDetalle: '
                . $this->conn->error
            );

            return false;
        }

        $stmt->bind_param(
            "iidsdd",
            $pedidoId,
            $productoId,
            $cantidad,
            $unidadVenta,
            $precioUnitario,
            $subtotal
        );

        $resultado = $stmt->execute();

        if (!$resultado) {

            error_log(
                'Error ejecutando agregarDetalle: '
                . $stmt->error
            );
        }

        $filasAfectadas = $stmt->affected_rows;
        $stmt->close();

        return $resultado && $filasAfectadas === 1;
    }


    /* OBTENER PEDIDOS DE UN USUARIO */
    public function obtenerPorUsuario($usuarioId)
    {
        $columnasPedido = $this->columnasDisponibles('pedidos', ['tipo']);
        $tipoSelect = in_array('tipo', $columnasPedido, true)
            ? "COALESCE(p.tipo, 'normal') AS tipo"
            : "'normal' AS tipo";

        $sql = "
            SELECT
                p.id,
                p.usuario_id,
                p.fecha_pedido,
                p.fecha_recepcion,
                p.franja_horaria,
                p.direccion_entrega,
                p.metodo_pago,
                p.estado,
                p.total,
                $tipoSelect

            FROM pedidos p

            WHERE p.usuario_id = ?

            ORDER BY p.fecha_pedido DESC
        ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param(
            "i",
            $usuarioId
        );

        if (!$stmt->execute()) {

            $stmt->close();

            return [];
        }

        $resultado = $stmt->get_result();

        $pedidos = $resultado->fetch_all(
            MYSQLI_ASSOC
        );

        $stmt->close();

        return $pedidos;
    }


    /* OBTENER PEDIDO POR ID - CLIENTE */
    public function obtenerPorId(
        $pedidoId,
        $usuarioId
    ) {

        $columnasPedido = $this->columnasDisponibles('pedidos', ['tipo']);
        $tipoSelect = in_array('tipo', $columnasPedido, true)
            ? "COALESCE(tipo, 'normal') AS tipo"
            : "'normal' AS tipo";

        $sql = "
            SELECT
                id,
                usuario_id,
                fecha_pedido,
                fecha_recepcion,
                franja_horaria,
                direccion_entrega,
                metodo_pago,
                estado,
                total,
                $tipoSelect

            FROM pedidos

            WHERE id = ?

            AND usuario_id = ?

            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param(
            "ii",
            $pedidoId,
            $usuarioId
        );

        if (!$stmt->execute()) {

            $stmt->close();

            return null;
        }

        $resultado = $stmt->get_result();

        $pedido = $resultado->fetch_assoc();

        $stmt->close();

        return $pedido;
    }


    /* OBTENER PEDIDO POR ID - ADMIN / EMPLEADO */
    public function obtenerPorIdAdmin($pedidoId)
    {

        $columnasPedido = $this->columnasDisponibles(
            'pedidos',
            ['tipo', 'nombre_cliente', 'telefono']
        );
        $tipoSelect = in_array('tipo', $columnasPedido, true)
            ? "COALESCE(p.tipo, 'normal') AS tipo"
            : "'normal' AS tipo";
        $nombreSelect = in_array('nombre_cliente', $columnasPedido, true)
            ? "COALESCE(NULLIF(p.nombre_cliente, ''), u.nombre_completo) AS nombre_completo"
            : 'u.nombre_completo AS nombre_completo';
        $telefonoSelect = in_array('telefono', $columnasPedido, true)
            ? "COALESCE(NULLIF(p.telefono, ''), u.telefono) AS telefono"
            : 'u.telefono AS telefono';

        $sql = "
            SELECT
                p.id,
                p.usuario_id,
                p.fecha_pedido,
                p.fecha_recepcion,
                p.franja_horaria,
                p.direccion_entrega,
                p.metodo_pago,
                p.estado,
                p.total,
                $tipoSelect,

                $nombreSelect,
                u.nombre_comercio,
                u.email,
                $telefonoSelect

            FROM pedidos p

            INNER JOIN usuarios u
                ON p.usuario_id = u.id

            WHERE p.id = ?

            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param(
            "i",
            $pedidoId
        );

        if (!$stmt->execute()) {

            $stmt->close();

            return null;
        }

        $resultado = $stmt->get_result();

        $pedido = $resultado->fetch_assoc();

        $stmt->close();

        return $pedido;
    }


    /* OBTENER DETALLES DE UN PEDIDO */
    public function obtenerDetalles($pedidoId)
    {

        $columnasDetalle = $this->columnasDisponibles(
            'pedido_detalles',
            ['seleccionada', 'estado_produccion', 'sector', 'sucursal']
        );
        $seleccionadaSelect = in_array('seleccionada', $columnasDetalle, true)
            ? 'pd.seleccionada'
            : '0 AS seleccionada';
        $estadoProduccionSelect = in_array('estado_produccion', $columnasDetalle, true)
            ? 'pd.estado_produccion'
            : "'pendiente' AS estado_produccion";
        $sectorSelect = in_array('sector', $columnasDetalle, true)
            ? 'pd.sector'
            : "'Por definir' AS sector";
        $sucursalSelect = in_array('sucursal', $columnasDetalle, true)
            ? 'pd.sucursal'
            : 'NULL AS sucursal';

        $sql = "
            SELECT
                pd.id,
                pd.pedido_id,
                pd.producto_id,
                pd.cantidad,
                pd.unidad_venta,
                pd.precio_unitario,
                pd.subtotal,
                $seleccionadaSelect,
                $estadoProduccionSelect,
                $sectorSelect,
                $sucursalSelect,

                p.nombre,
                p.imagen

            FROM pedido_detalles pd

            INNER JOIN productos p
                ON pd.producto_id = p.id

            WHERE pd.pedido_id = ?

            ORDER BY pd.id ASC
        ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param(
            "i",
            $pedidoId
        );

        if (!$stmt->execute()) {

            $stmt->close();

            return [];
        }

        $resultado = $stmt->get_result();

        $detalles = $resultado->fetch_all(
            MYSQLI_ASSOC
        );

        $stmt->close();

        return $detalles;
    }

    public function guardarProduccionPedido($pedidoId, array $detalleIds = [], array $sectores = [], array $sucursales = [])
    {
        $pedidoId = (int) $pedidoId;
        if ($pedidoId <= 0) {
            return false;
        }

        if (!$this->conn->begin_transaction()) {
            return false;
        }

        try {
            $stmtReset = $this->conn->prepare('UPDATE pedido_detalles SET seleccionada = 0 WHERE pedido_id = ?');
            if (!$stmtReset) {
                throw new RuntimeException('No se pudo limpiar la selección de producción.');
            }
            $stmtReset->bind_param('i', $pedidoId);
            if (!$stmtReset->execute()) {
                $stmtReset->close();
                throw new RuntimeException('No se pudo limpiar la selección de producción.');
            }
            $stmtReset->close();

            if (!empty($detalleIds)) {
                $idsUnicos = [];
                foreach ($detalleIds as $detalleId) {
                    $detalleId = (int) $detalleId;
                    if ($detalleId > 0) {
                        $idsUnicos[] = $detalleId;
                    }
                }

                $idsUnicos = array_values(array_unique($idsUnicos));
                if (!empty($idsUnicos)) {
                    $marcadores = implode(',', array_fill(0, count($idsUnicos), '?'));
                    $sql = 'UPDATE pedido_detalles SET seleccionada = 1 WHERE pedido_id = ? AND id IN (' . $marcadores . ')';
                    $stmt = $this->conn->prepare($sql);
                    if (!$stmt) {
                        throw new RuntimeException('No se pudo actualizar los productos seleccionados.');
                    }

                    $tipos = 'i' . str_repeat('i', count($idsUnicos));
                    $params = [$tipos, $pedidoId];
                    foreach ($idsUnicos as $id) {
                        $params[] = $id;
                    }

                    $referencias = [];
                    foreach ($params as $k => $v) {
                        $referencias[$k] = &$params[$k];
                    }

                    call_user_func_array([$stmt, 'bind_param'], $referencias);
                    if (!$stmt->execute()) {
                        $stmt->close();
                        throw new RuntimeException('No se pudo marcar los productos seleccionados.');
                    }
                    $stmt->close();
                }
            }

            if (!empty($sectores) || !empty($sucursales)) {
                foreach ($sectores as $detalleId => $sector) {
                    $detalleId = (int) $detalleId;
                    if ($detalleId <= 0) {
                        continue;
                    }
                    $sectorValor = trim((string) ($sector ?? 'Por definir'));
                    $sucursalValor = trim((string) ($sucursales[$detalleId] ?? ''));

                    $stmtUpdate = $this->conn->prepare(
                        'UPDATE pedido_detalles SET sector = ?, sucursal = ? WHERE pedido_id = ? AND id = ?'
                    );
                    if (!$stmtUpdate) {
                        throw new RuntimeException('No se pudo guardar la asignación del sector.');
                    }

                    $stmtUpdate->bind_param('ssii', $sectorValor, $sucursalValor, $pedidoId, $detalleId);
                    if (!$stmtUpdate->execute()) {
                        $stmtUpdate->close();
                        throw new RuntimeException('No se pudo guardar la asignación del sector.');
                    }
                    $stmtUpdate->close();
                }
            }

            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            $this->conn->rollback();
            error_log('Error guardando producción del pedido: ' . $e->getMessage());
            return false;
        }
    }

    public function actualizarEstadoProduccion($detalleId, $estado)
    {
        $estadosPermitidos = ['pendiente', 'en_produccion', 'terminado'];
        $detalleId = (int) $detalleId;
        if ($detalleId <= 0 || !in_array($estado, $estadosPermitidos, true)) {
            return false;
        }

        $stmt = $this->conn->prepare(
            'UPDATE pedido_detalles SET estado_produccion = ? WHERE id = ? AND seleccionada = 1'
        );
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('si', $estado, $detalleId);
        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

    public function obtenerDetallesDeUsuario($pedidoId, $usuarioId)
    {
        $sql = "
            SELECT pd.producto_id, pd.cantidad, pd.unidad_venta
            FROM pedido_detalles pd
            INNER JOIN pedidos p ON p.id = pd.pedido_id
            WHERE pd.pedido_id = ? AND p.usuario_id = ?
            ORDER BY pd.id ASC
        ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('ii', $pedidoId, $usuarioId);

        if (!$stmt->execute()) {
            $stmt->close();
            return [];
        }

        $detalles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $detalles;
    }


    /* OBTENER PEDIDO COMPLETO */
    public function obtenerCompleto(
        $pedidoId,
        $usuarioId
    ) {

        $pedido = $this->obtenerPorId(
            $pedidoId,
            $usuarioId
        );

        if (!$pedido) {
            return null;
        }

        $pedido['detalles'] =
            $this->obtenerDetalles(
                $pedidoId
            );

        return $pedido;
    }


    /* OBTENER TODOS LOS PEDIDOS */
    public function obtenerTodos($nombreCliente = '', $fechaDesde = '', $fechaHasta = '', $estado = '')
    {

        $columnasPedido = $this->columnasDisponibles(
            'pedidos',
            ['tipo', 'nombre_cliente', 'telefono']
        );
        $tipoSelect = in_array('tipo', $columnasPedido, true)
            ? "COALESCE(p.tipo, 'normal') AS tipo"
            : "'normal' AS tipo";
        $nombreSelect = in_array('nombre_cliente', $columnasPedido, true)
            ? "COALESCE(NULLIF(p.nombre_cliente, ''), u.nombre_completo) AS nombre_completo"
            : 'u.nombre_completo AS nombre_completo';
        $telefonoSelect = in_array('telefono', $columnasPedido, true)
            ? "COALESCE(NULLIF(p.telefono, ''), u.telefono) AS telefono"
            : 'u.telefono AS telefono';

        $sql = "
            SELECT
                p.id,
                p.usuario_id,
                p.fecha_pedido,
                p.fecha_recepcion,
                p.franja_horaria,
                p.direccion_entrega,
                p.metodo_pago,
                p.estado,
                p.total,
                $tipoSelect,

                $nombreSelect,
                u.nombre_comercio,
                u.email,
                $telefonoSelect

            FROM pedidos p

            INNER JOIN usuarios u
                ON p.usuario_id = u.id
        ";

        $condiciones = [];
        $parametros = [];
        $tipos = '';

        if ($nombreCliente !== '') {
            $nombreFiltro = in_array('nombre_cliente', $columnasPedido, true)
                ? "COALESCE(NULLIF(p.nombre_cliente, ''), u.nombre_completo)"
                : 'u.nombre_completo';
            $condiciones[] = $nombreFiltro . ' LIKE ?';
            $parametros[] = '%' . $nombreCliente . '%';
            $tipos .= 's';
        }

        if ($fechaDesde !== '') {
            $condiciones[] = 'DATE(p.fecha_pedido) >= ?';
            $parametros[] = $fechaDesde;
            $tipos .= 's';
        }

        if ($fechaHasta !== '') {
            $condiciones[] = 'DATE(p.fecha_pedido) <= ?';
            $parametros[] = $fechaHasta;
            $tipos .= 's';
        }

        if ($estado !== '') {
            $condiciones[] = 'p.estado = ?';
            $parametros[] = $estado;
            $tipos .= 's';
        }

        if (!empty($condiciones)) {
            $sql .= ' WHERE ' . implode(' AND ', $condiciones);
        }

        $sql .= ' ORDER BY p.fecha_pedido DESC';

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return [];
        }

        if (!empty($parametros)) {
            $stmt->bind_param($tipos, ...$parametros);
        }

        if (!$stmt->execute()) {
            $stmt->close();
            return [];
        }

        $pedidos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $pedidos;
    }


    /* ACTUALIZAR ESTADO */
    public function actualizarEstado(
        $pedidoId,
        $estado
    ) {

        $estadosPermitidos = [

            'pendiente',
            'confirmado',
            'en_preparacion',
            'listo',
            'entregado',
            'cancelado'

        ];

        if (
            !in_array(
                $estado,
                $estadosPermitidos,
                true
            )
        ) {

            return false;
        }

        $pedidoId = (int) $pedidoId;

        if ($pedidoId <= 0) {
            return false;
        }

        $pedido = $this->obtenerPorIdAdmin($pedidoId);

        if (!$pedido) {
            return false;
        }

        $transiciones = [
            'pendiente' => ['confirmado', 'cancelado'],
            'confirmado' => ['en_preparacion', 'cancelado'],
            'en_preparacion' => ['listo', 'cancelado'],
            'listo' => ['entregado'],
            'entregado' => [],
            'cancelado' => []
        ];

        if (!in_array($estado, $transiciones[$pedido['estado']], true)) {
            return false;
        }

        if ($estado === 'cancelado') {
            return $this->cancelarPedidoSeguro($pedidoId);
        }

        $sql = "
            UPDATE pedidos

            SET estado = ?

            WHERE id = ?
        ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {

            error_log(
                'Error preparando actualizarEstado: '
                . $this->conn->error
            );

            return false;
        }

        $stmt->bind_param(
            "si",
            $estado,
            $pedidoId
        );

        $resultado = $stmt->execute();

        if (!$resultado) {

            error_log(
                'Error ejecutando actualizarEstado: '
                . $stmt->error
            );
        }

        $filasAfectadas = $stmt->affected_rows;
        $stmt->close();

        return $resultado && $filasAfectadas === 1;
    }


    /* CANCELAR PEDIDO */
    public function cancelarPedido(
        $pedidoId,
        $usuarioId
    ) {
        return $this->cancelarPedidoSeguro($pedidoId, $usuarioId);
    }

    /**
     * Cancela un pedido pendiente y devuelve sus cantidades al stock.
     * La transacción evita que el pedido quede cancelado sin recuperar inventario.
*/
    private function cancelarPedidoSeguro($pedidoId, $usuarioId = null)
    {
        $pedidoId = (int) $pedidoId;
        $usuarioId = $usuarioId === null ? null : (int) $usuarioId;

        if ($pedidoId <= 0 || ($usuarioId !== null && $usuarioId <= 0)) {
            return false;
        }

        if (!$this->conn->begin_transaction()) {
            return false;
        }

        try {
            $sqlPedido = $usuarioId === null
                ? "SELECT estado FROM pedidos WHERE id = ? FOR UPDATE"
                : "SELECT estado FROM pedidos WHERE id = ? AND usuario_id = ? FOR UPDATE";
            $stmtPedido = $this->conn->prepare($sqlPedido);

            if (!$stmtPedido) {
                throw new RuntimeException('No se pudo bloquear el pedido.');
            }

            if ($usuarioId === null) {
                $stmtPedido->bind_param('i', $pedidoId);
            } else {
                $stmtPedido->bind_param('ii', $pedidoId, $usuarioId);
            }

            if (!$stmtPedido->execute()) {
                $stmtPedido->close();
                throw new RuntimeException('No se pudo consultar el pedido.');
            }

            $pedido = $stmtPedido->get_result()->fetch_assoc();
            $stmtPedido->close();

            if (!$pedido || $pedido['estado'] !== 'pendiente') {
                throw new RuntimeException('El pedido ya no está pendiente.');
            }

            $stmtDetalles = $this->conn->prepare(
                'SELECT producto_id, cantidad, unidad_venta FROM pedido_detalles WHERE pedido_id = ? FOR UPDATE'
            );
            if (!$stmtDetalles) {
                throw new RuntimeException('No se pudieron consultar los detalles.');
            }
            $stmtDetalles->bind_param('i', $pedidoId);
            if (!$stmtDetalles->execute()) {
                $stmtDetalles->close();
                throw new RuntimeException('No se pudieron consultar los detalles.');
            }
            $detalles = $stmtDetalles->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmtDetalles->close();

            foreach ($detalles as $detalle) {
                $cantidad = (float) $detalle['cantidad'];
                $productoId = (int) $detalle['producto_id'];
                $stmtProducto = $this->conn->prepare(
                    'SELECT stock, unidad_venta, stocks_modalidades FROM productos WHERE id = ? FOR UPDATE'
                );
                if (!$stmtProducto) {
                    throw new RuntimeException('No se pudo devolver el stock.');
                }
                $stmtProducto->bind_param('i', $productoId);
                if (!$stmtProducto->execute()) {
                    $stmtProducto->close();
                    throw new RuntimeException('No se pudo consultar el stock a devolver.');
                }
                $producto = $stmtProducto->get_result()->fetch_assoc();
                $stmtProducto->close();
                if (!$producto) {
                    throw new RuntimeException('No se pudo devolver el stock de un producto eliminado.');
                }

                $unidadVenta = (string) ($detalle['unidad_venta'] ?? $producto['unidad_venta']);
                $stocksPorModalidad = Producto::obtenerStocks($producto);
                $consumo = Producto::consumoInventario($unidadVenta, $cantidad);
                $stocksPorModalidad[$consumo['base']] += $consumo['cantidad'];
                $stocksPorModalidad[$consumo['base']] = round(
                    $stocksPorModalidad[$consumo['base']],
                    $consumo['base'] === 'kilogramo' ? 1 : 0
                );
                $stocksJson = json_encode($stocksPorModalidad);
                $stockTotal = Producto::stockCompatibilidad($stocksPorModalidad);
                $stmtStock = $this->conn->prepare(
                    'UPDATE productos SET stocks_modalidades = ?, stock = ? WHERE id = ?'
                );
                if (!$stmtStock) {
                    throw new RuntimeException('No se pudo preparar la devolución de stock.');
                }
                $stmtStock->bind_param('sii', $stocksJson, $stockTotal, $productoId);
                if (!$stmtStock->execute() || $stmtStock->affected_rows !== 1) {
                    $stmtStock->close();
                    throw new RuntimeException('No se pudo devolver el stock.');
                }
                $stmtStock->close();
            }

            $stmtCancelar = $this->conn->prepare(
                'UPDATE pedidos SET estado = \'cancelado\' WHERE id = ? AND estado = \'pendiente\''
            );
            if (!$stmtCancelar) {
                throw new RuntimeException('No se pudo cancelar el pedido.');
            }
            $stmtCancelar->bind_param('i', $pedidoId);
            if (!$stmtCancelar->execute() || $stmtCancelar->affected_rows !== 1) {
                $stmtCancelar->close();
                throw new RuntimeException('No se pudo cancelar el pedido.');
            }
            $stmtCancelar->close();

            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            $this->conn->rollback();
            error_log('Error cancelando pedido y devolviendo stock: ' . $e->getMessage());
            return false;
        }
    }


    public function crearPedidoEncargo($usuarioId, $datos, $detalles)
    {
        if (empty($detalles)) {
            $this->ultimoError = 'Debés cargar al menos un producto.';
            return false;
        }

        $nombreCliente = trim((string) ($datos['nombre_cliente'] ?? ''));
        $telefono = trim((string) ($datos['telefono'] ?? ''));
        $observaciones = trim((string) ($datos['observaciones'] ?? ''));
        $sucursal = trim((string) ($datos['sucursal'] ?? ''));
        $fechaEntrega = trim((string) ($datos['fecha_entrega'] ?? ''));
        $direccionEntrega = trim((string) ($datos['direccion_entrega'] ?? ''));

        if ($nombreCliente === '' || $telefono === '') {
            $this->ultimoError = 'El nombre y teléfono del cliente son obligatorios.';
            return false;
        }

        $total = 0.0;
        $itemsValidados = [];

        foreach ($detalles as $detalle) {
            $productoId = (int) ($detalle['producto_id'] ?? 0);
            $cantidad = (float) ($detalle['cantidad'] ?? 0);
            $unidadVenta = (string) ($detalle['unidad_venta'] ?? 'unidad');

            if ($productoId <= 0 || $cantidad <= 0) {
                $this->ultimoError = 'Hay productos inválidos en este pedido por encargo.';
                return false;
            }

            $producto = $this->conn->query('SELECT id, nombre, presentaciones, unidad_venta FROM productos WHERE id = ' . $productoId . ' LIMIT 1')->fetch_assoc();
            if (!$producto) {
                $this->ultimoError = 'Uno de los productos no existe.';
                return false;
            }

            $precios = Producto::obtenerPresentaciones($producto);
            if (!isset($precios[$unidadVenta])) {
                $this->ultimoError = 'La modalidad de venta seleccionada no está disponible.';
                return false;
            }

            $precio = (float) $precios[$unidadVenta];
            $subtotal = $precio * $cantidad;
            $itemsValidados[] = [
                'producto_id' => $productoId,
                'cantidad' => $cantidad,
                'unidad_venta' => $unidadVenta,
                'precio_unitario' => $precio,
                'subtotal' => $subtotal,
                'nombre' => $producto['nombre']
            ];
            $total += $subtotal;
        }

        if (!$this->conn->begin_transaction()) {
            $this->ultimoError = 'No se pudo iniciar la transacción.';
            return false;
        }

        try {
            $stmtPedido = $this->conn->prepare(
                'INSERT INTO pedidos (usuario_id, fecha_pedido, fecha_recepcion, franja_horaria, direccion_entrega, metodo_pago, estado, total, tipo, nombre_cliente, telefono, observaciones, fecha_entrega, sucursal) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, \'encargo\', ?, ?, ?, ?, ?)'
            );
            if (!$stmtPedido) {
                throw new RuntimeException('No se pudo preparar el pedido original.');
            }

            $fechaRecepcion = $fechaEntrega !== '' ? $fechaEntrega : date('Y-m-d');
            $fechaEntregaDb = $fechaEntrega !== '' ? $fechaEntrega : null;
            $franjaHoraria = 'A coordinar';
            $estadoInicial = 'pendiente';
            $metodoPago = 'efectivo';
            $stmtPedido->bind_param(
                'isssssdsssss',
                $usuarioId,
                $fechaRecepcion,
                $franjaHoraria,
                $direccionEntrega,
                $metodoPago,
                $estadoInicial,
                $total,
                $nombreCliente,
                $telefono,
                $observaciones,
                $fechaEntregaDb,
                $sucursal
            );
            if (!$stmtPedido->execute()) {
                throw new RuntimeException('No se pudo guardar el pedido.');
            }
            $pedidoId = $this->conn->insert_id;
            $stmtPedido->close();

            $detalleColumns = $this->columnasDisponibles('pedido_detalles', ['seleccionada', 'sector', 'sucursal']);
            foreach ($itemsValidados as $item) {
                $sql = 'INSERT INTO pedido_detalles (pedido_id, producto_id, cantidad, unidad_venta, precio_unitario, subtotal';
                $params = [$pedidoId, $item['producto_id'], $item['cantidad'], $item['unidad_venta'], $item['precio_unitario'], $item['subtotal']];
                $types = 'iidsdd';
                if (in_array('seleccionada', $detalleColumns, true)) {
                    $sql .= ', seleccionada';
                    $params[] = 0;
                    $types .= 'i';
                }
                if (in_array('sector', $detalleColumns, true)) {
                    $sql .= ', sector';
                    $params[] = 'Por definir';
                    $types .= 's';
                }
                if (in_array('sucursal', $detalleColumns, true)) {
                    $sql .= ', sucursal';
                    $params[] = $sucursal;
                    $types .= 's';
                }
                $sql .= ') VALUES (?,?,?,?,?';
                if (in_array('seleccionada', $detalleColumns, true)) { $sql .= ',?'; }
                if (in_array('sector', $detalleColumns, true)) { $sql .= ',?'; }
                if (in_array('sucursal', $detalleColumns, true)) { $sql .= ',?'; }
                $sql .= ')';

                $stmtDetalle = $this->conn->prepare($sql);
                if (!$stmtDetalle) {
                    throw new RuntimeException('No se pudo guardar un detalle del pedido por encargo.');
                }
                $stmtDetalle->bind_param($types, ...$params);
                if (!$stmtDetalle->execute()) {
                    throw new RuntimeException('No se pudo completar un detalle del pedido por encargo.');
                }
                $stmtDetalle->close();
            }

            $this->conn->commit();
            return $pedidoId;
        } catch (Throwable $e) {
            $this->conn->rollback();
            $this->ultimoError = $e->getMessage();
            error_log('Error creando encargo: ' . $e->getMessage());
            return false;
        }
    }

    public function produccionConfigurada(): bool
    {
        $columnas = $this->columnasDisponibles(
            'pedido_detalles',
            ['seleccionada', 'estado_produccion', 'sector', 'sucursal']
        );

        return count($columnas) === 4;
    }

    public function obtenerProduccion($estado = '')
    {
        if (!$this->tablaExiste('pedido_detalles')) {
            return [];
        }

        $columnasDetalle = $this->columnasDisponibles(
            'pedido_detalles',
            ['seleccionada', 'estado_produccion', 'sector', 'sucursal']
        );
        if (!in_array('seleccionada', $columnasDetalle, true)) {
            return [];
        }

        $columnasPedido = $this->columnasDisponibles(
            'pedidos',
            ['tipo', 'nombre_cliente', 'telefono', 'sucursal']
        );
        $estadoSelect = in_array('estado_produccion', $columnasDetalle, true)
            ? 'pd.estado_produccion'
            : "'pendiente' AS estado_produccion";
        $sectorSelect = in_array('sector', $columnasDetalle, true)
            ? 'pd.sector'
            : "'Por definir' AS sector";
        $sucursalDetalleSelect = in_array('sucursal', $columnasDetalle, true)
            ? "NULLIF(pd.sucursal, '')"
            : 'NULL';
        $sucursalPedidoSelect = in_array('sucursal', $columnasPedido, true)
            ? 'ped.sucursal'
            : 'NULL';
        $nombreClienteSelect = in_array('nombre_cliente', $columnasPedido, true)
            ? "COALESCE(NULLIF(ped.nombre_cliente, ''), u.nombre_completo)"
            : 'u.nombre_completo';
        $telefonoSelect = in_array('telefono', $columnasPedido, true)
            ? 'COALESCE(NULLIF(ped.telefono, \'\'), u.telefono)'
            : 'u.telefono';
        $tipoSelect = in_array('tipo', $columnasPedido, true)
            ? "COALESCE(ped.tipo, 'normal')"
            : "'normal'";

        $sql = "
            SELECT
                pd.id,
                pd.pedido_id,
                pd.producto_id,
                pd.cantidad,
                pd.unidad_venta,
                pd.seleccionada,
                $estadoSelect,
                $sectorSelect,
                COALESCE($sucursalDetalleSelect, $sucursalPedidoSelect) AS sucursal,
                p.nombre,
                p.imagen,
                ped.fecha_recepcion,
                ped.fecha_pedido,
                $nombreClienteSelect AS nombre_cliente,
                u.nombre_comercio,
                $telefonoSelect AS telefono,
                $tipoSelect AS tipo_pedido
            FROM pedido_detalles pd
            INNER JOIN productos p ON p.id = pd.producto_id
            INNER JOIN pedidos ped ON ped.id = pd.pedido_id
            INNER JOIN usuarios u ON u.id = ped.usuario_id
            WHERE pd.seleccionada = 1
        ";

        if (
            in_array('estado_produccion', $columnasDetalle, true) &&
            $estado !== '' &&
            in_array($estado, ['pendiente', 'en_produccion', 'terminado'], true)
        ) {
            $sql .= ' AND pd.estado_produccion = ?';
            $sql .= ' ORDER BY ped.fecha_recepcion ASC, pd.pedido_id ASC, pd.id ASC';
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                return [];
            }
            $stmt->bind_param('s', $estado);
            if (!$stmt->execute()) {
                $stmt->close();
                return [];
            }

            $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $items;
        }

        $sql .= ' ORDER BY ped.fecha_recepcion ASC, pd.pedido_id ASC, pd.id ASC';

        $resultado = $this->conn->query($sql);
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    /* MARCAR PEDIDO COMO PAGADO */
    public function marcarComoPagado($pedidoId)
    {

        $pedidoId = (int) $pedidoId;

        if ($pedidoId <= 0) {
            return false;
        }

        $sql = "
            UPDATE pedidos

            SET estado = 'confirmado'

            WHERE id = ?

            AND estado = 'pendiente'
        ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {

            error_log(
                'Error preparando marcarComoPagado: '
                . $this->conn->error
            );

            return false;
        }

        $stmt->bind_param(
            "i",
            $pedidoId
        );

        $resultado = $stmt->execute();

        if (!$resultado) {

            error_log(
                'Error ejecutando marcarComoPagado: '
                . $stmt->error
            );
        }

        $stmt->close();

        return $resultado;
    }
}