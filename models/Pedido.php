<?php

class Pedido
{
    private $conn;
    private $ultimoError = '';


    /* =========================================================
       CONSTRUCTOR
    ========================================================= */

    public function __construct($conn)
    {
        $this->conn = $conn;
    }


    /* =========================================================
       CREAR PEDIDO
    ========================================================= */

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
                "SELECT id, nombre, precio, stock, activo FROM productos WHERE id IN ($marcadores) FOR UPDATE"
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
            $total = 0;
            foreach ($detalles as $detalle) {
                $productoId = (int) ($detalle['producto_id'] ?? 0);
                $cantidad = filter_var($detalle['cantidad'] ?? null, FILTER_VALIDATE_INT);
                $producto = $porId[$productoId] ?? null;

                if (!$producto || (int) $producto['activo'] !== 1) {
                    throw new RuntimeException('El producto seleccionado ya no está disponible.');
                }
                if ($cantidad === false || $cantidad <= 0 || $cantidad > (int) $producto['stock']) {
                    throw new RuntimeException('Stock insuficiente para: ' . $producto['nombre']);
                }

                $precio = (float) $producto['precio'];
                $detallesRevalidados[] = [
                    'producto_id' => $productoId,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio
                ];
                $total += $precio * $cantidad;
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
                    $detalle['precio_unitario'],
                    $subtotal
                )) {
                    throw new RuntimeException('No se pudo guardar un detalle.');
                }

                $stmtStock = $this->conn->prepare(
                    'UPDATE productos SET stock = stock - ? WHERE id = ? AND stock >= ?'
                );
                if (!$stmtStock) {
                    throw new RuntimeException('No se pudo descontar el stock.');
                }
                $stmtStock->bind_param(
                    'iii',
                    $detalle['cantidad'],
                    $detalle['producto_id'],
                    $detalle['cantidad']
                );
                if (!$stmtStock->execute() || $stmtStock->affected_rows !== 1) {
                    $stmtStock->close();
                    throw new RuntimeException('Stock insuficiente para el producto.');
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
        $sql = "SELECT id, precio FROM productos WHERE activo = 1 AND id IN ($marcadores)";
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


    /* =========================================================
       CONTAR PEDIDOS PENDIENTES
    ========================================================= */

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


    /* =========================================================
       AGREGAR DETALLE
    ========================================================= */

    public function agregarDetalle(
        $pedidoId,
        $productoId,
        $cantidad,
        $precioUnitario,
        $subtotal
    ) {

        $sql = "
            INSERT INTO pedido_detalles
            (
                pedido_id,
                producto_id,
                cantidad,
                precio_unitario,
                subtotal
            )
            VALUES
            (
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
            "iiidd",
            $pedidoId,
            $productoId,
            $cantidad,
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


    /* =========================================================
       GUARDAR MERCADO PAGO ORDER ID
    ========================================================= */

    public function guardarMercadoPagoOrderId(
        $pedidoId,
        $orderId
    ) {

        $pedidoId = (int) $pedidoId;

        $orderId = trim(
            (string) $orderId
        );

        if (
            $pedidoId <= 0 ||
            $orderId === ''
        ) {
            return false;
        }

        if (strlen($orderId) > 100) {
            return false;
        }

        $sql = "
            UPDATE pedidos

            SET mercado_pago_order_id = ?

            WHERE id = ?
        ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {

            error_log(
                'Error preparando guardarMercadoPagoOrderId: '
                . $this->conn->error
            );

            return false;
        }

        $stmt->bind_param(
            "si",
            $orderId,
            $pedidoId
        );

        $resultado = $stmt->execute();

        if (!$resultado) {

            error_log(
                'Error ejecutando guardarMercadoPagoOrderId: '
                . $stmt->error
            );
        }

        $filasAfectadas = $stmt->affected_rows;
        $stmt->close();

        return $resultado && $filasAfectadas === 1;
    }


    /* =========================================================
       GUARDAR MERCADO PAGO PAYMENT ID
    ========================================================= */

    public function guardarMercadoPagoPaymentId(
        $pedidoId,
        $paymentId
    ) {

        $pedidoId = (int) $pedidoId;

        $paymentId = trim(
            (string) $paymentId
        );

        if (
            $pedidoId <= 0 ||
            $paymentId === ''
        ) {
            return false;
        }

        if (strlen($paymentId) > 100) {
            return false;
        }

        $sql = "
            UPDATE pedidos

            SET mercado_pago_payment_id = ?

            WHERE id = ?
        ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {

            error_log(
                'Error preparando guardarMercadoPagoPaymentId: '
                . $this->conn->error
            );

            return false;
        }

        $stmt->bind_param(
            "si",
            $paymentId,
            $pedidoId
        );

        $resultado = $stmt->execute();

        if (!$resultado) {

            error_log(
                'Error ejecutando guardarMercadoPagoPaymentId: '
                . $stmt->error
            );
        }

        $stmt->close();

        return $resultado;
    }


    /* =========================================================
       OBTENER PEDIDOS DE UN USUARIO
    ========================================================= */

    public function obtenerPorUsuario($usuarioId)
    {

        $sql = "
            SELECT
                id,
                usuario_id,
                fecha_pedido,
                fecha_recepcion,
                franja_horaria,
                direccion_entrega,
                metodo_pago,
                mercado_pago_order_id,
                mercado_pago_payment_id,
                estado,
                total

            FROM pedidos

            WHERE usuario_id = ?

            ORDER BY fecha_pedido DESC
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


    /* =========================================================
       OBTENER PEDIDO POR ID - CLIENTE
    ========================================================= */

    public function obtenerPorId(
        $pedidoId,
        $usuarioId
    ) {

        $sql = "
            SELECT
                id,
                usuario_id,
                fecha_pedido,
                fecha_recepcion,
                franja_horaria,
                direccion_entrega,
                metodo_pago,
                mercado_pago_order_id,
                mercado_pago_payment_id,
                estado,
                total

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


    /* =========================================================
       OBTENER PEDIDO POR ID - ADMIN / EMPLEADO
    ========================================================= */

    public function obtenerPorIdAdmin($pedidoId)
    {

        $sql = "
            SELECT
                p.id,
                p.usuario_id,
                p.fecha_pedido,
                p.fecha_recepcion,
                p.franja_horaria,
                p.direccion_entrega,
                p.metodo_pago,
                p.mercado_pago_order_id,
                p.mercado_pago_payment_id,
                p.estado,
                p.total,

                u.nombre_completo,
                u.nombre_comercio,
                u.email,
                u.telefono

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


    /* =========================================================
       OBTENER DETALLES DE UN PEDIDO
    ========================================================= */

    public function obtenerDetalles($pedidoId)
    {

        $sql = "
            SELECT
                pd.id,
                pd.pedido_id,
                pd.producto_id,
                pd.cantidad,
                pd.precio_unitario,
                pd.subtotal,

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

    public function obtenerDetallesDeUsuario($pedidoId, $usuarioId)
    {
        $sql = "
            SELECT pd.producto_id, pd.cantidad
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


    /* =========================================================
       OBTENER PEDIDO COMPLETO
    ========================================================= */

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


    /* =========================================================
       OBTENER TODOS LOS PEDIDOS
    ========================================================= */

    public function obtenerTodos()
    {

        $sql = "
            SELECT
                p.id,
                p.usuario_id,
                p.fecha_pedido,
                p.fecha_recepcion,
                p.franja_horaria,
                p.direccion_entrega,
                p.metodo_pago,
                p.mercado_pago_order_id,
                p.mercado_pago_payment_id,
                p.estado,
                p.total,

                u.nombre_completo,
                u.nombre_comercio,
                u.email,
                u.telefono

            FROM pedidos p

            INNER JOIN usuarios u
                ON p.usuario_id = u.id

            ORDER BY p.fecha_pedido DESC
        ";

        $resultado = $this->conn->query($sql);

        if (!$resultado) {
            return [];
        }

        return $resultado->fetch_all(
            MYSQLI_ASSOC
        );
    }


    /* =========================================================
       ACTUALIZAR ESTADO
    ========================================================= */

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


    /* =========================================================
       CANCELAR PEDIDO
    ========================================================= */

    public function cancelarPedido(
        $pedidoId,
        $usuarioId
    ) {

        $sql = "
            UPDATE pedidos

            SET estado = 'cancelado'

            WHERE id = ?

            AND usuario_id = ?

            AND estado = 'pendiente'
        ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "ii",
            $pedidoId,
            $usuarioId
        );

        $resultado = $stmt->execute();
        $filasAfectadas = $stmt->affected_rows;

        $stmt->close();

        return $resultado && $filasAfectadas === 1;
    }


    /* =========================================================
       MARCAR PEDIDO COMO PAGADO
    ========================================================= */

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