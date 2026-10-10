<?php

class Produccion
{
    private const ESTADOS_COMERCIALES = [
        'pendiente',
        'confirmado',
        'en_preparacion',
        'listo',
        'entregado'
    ];

    private mysqli $conn;
    private string $ultimoError = '';

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function obtenerUltimoError(): string
    {
        return $this->ultimoError;
    }

    public function validarMetadatos(array $entrada): array|false
    {
        return $this->normalizarMetadatos($entrada);
    }

    public function esquemaDisponible(): bool
    {
        $resultado = $this->conn->query("SHOW TABLES LIKE 'tandas_produccion'");
        if (!$resultado || $resultado->num_rows !== 1) {
            return false;
        }

        $resultado = $this->conn->query("SHOW TABLES LIKE 'tandas_produccion_detalles'");
        return $resultado && $resultado->num_rows === 1;
    }

    public function metadatosDisponibles(): bool
    {
        $resultado = $this->conn->query(
            "SELECT COUNT(*) AS cantidad FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tandas_produccion'
             AND COLUMN_NAME IN ('titulo', 'destinos', 'observaciones', 'historial_metadatos')"
        );
        return $resultado && (int) $resultado->fetch_assoc()['cantidad'] === 4;
    }

    public function normalizarFiltroEstado($estado): string
    {
        if (!is_string($estado)) {
            return 'pendiente';
        }
        $estado = trim($estado);
        return $estado === '' || in_array($estado, self::ESTADOS_COMERCIALES, true)
            ? $estado
            : 'pendiente';
    }

    public function estadosComerciales(): array
    {
        return self::ESTADOS_COMERCIALES;
    }

    public function obtenerPedidos(string $estado = 'pendiente'): array
    {
        $estado = $this->normalizarFiltroEstado($estado);
        $sql = "
            SELECT
                ped.id,
                ped.fecha_pedido,
                ped.fecha_recepcion,
                ped.estado,
                COALESCE(ped.sucursal, '') AS sucursal,
                COALESCE(NULLIF(ped.nombre_cliente, ''), u.nombre_comercio, u.nombre_completo) AS cliente,
                COALESCE(ped.tipo, 'normal') AS tipo,
                COUNT(pd.id) AS cantidad_lineas,
                GROUP_CONCAT(CONCAT(pr.nombre, ' × ', pd.cantidad, ' ', pd.unidad_venta) ORDER BY pd.id SEPARATOR ', ') AS productos,
                SUM(CASE WHEN pd.id IS NOT NULL AND (
                    pd.seleccionada = 1 OR pd.estado_produccion <> 'pendiente' OR
                    COALESCE(asignadas.cantidad_asignada, 0) >= pd.cantidad
                ) THEN 1 ELSE 0 END) AS lineas_completas,
                SUM(CASE WHEN pd.id IS NOT NULL AND (
                    pd.seleccionada = 1 OR pd.estado_produccion <> 'pendiente' OR
                    COALESCE(asignadas.cantidad_asignada, 0) > 0
                ) THEN 1 ELSE 0 END) AS lineas_con_asignacion
            FROM pedidos ped
            INNER JOIN usuarios u ON u.id = ped.usuario_id
            LEFT JOIN pedido_detalles pd ON pd.pedido_id = ped.id
            LEFT JOIN productos pr ON pr.id = pd.producto_id
            LEFT JOIN (
                SELECT pedido_detalle_id, SUM(cantidad) AS cantidad_asignada
                FROM tandas_produccion_detalles
                GROUP BY pedido_detalle_id
            ) asignadas ON asignadas.pedido_detalle_id = pd.id
            WHERE ped.estado <> 'cancelado'
            GROUP BY ped.id
            ORDER BY ped.fecha_recepcion ASC, ped.id DESC
        ";
        if ($estado !== '') {
            $sql = str_replace("WHERE ped.estado <> 'cancelado'", 'WHERE ped.estado = ?', $sql);
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                return [];
            }
            $stmt->bind_param('s', $estado);
            if (!$stmt->execute()) {
                $stmt->close();
                return [];
            }
            $pedidos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        } else {
            $resultado = $this->conn->query($sql);
            $pedidos = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
        }

        foreach ($pedidos as &$pedido) {
            $totalLineas = (int) $pedido['cantidad_lineas'];
            $completas = (int) $pedido['lineas_completas'];
            $iniciadas = (int) $pedido['lineas_con_asignacion'];
            $pedido['estado_asignacion'] = $totalLineas === 0
                ? 'sin_productos'
                : ($completas === $totalLineas
                    ? 'completo'
                    : ($iniciadas > 0 ? 'parcial' : 'sin_asignar'));
        }
        unset($pedido);

        return $pedidos;
    }

    public function obtenerLineasPedidos(array $pedidoIds, string $estado = 'pendiente'): array
    {
        $estado = $this->normalizarFiltroEstado($estado);
        $pedidoIds = array_values(array_unique(array_filter(array_map('intval', $pedidoIds), static fn ($id) => $id > 0)));
        if ($pedidoIds === []) {
            return [];
        }

        $marcadores = implode(',', array_fill(0, count($pedidoIds), '?'));
        $sql = "
            SELECT
                pd.id AS detalle_id,
                pd.pedido_id,
                pd.producto_id,
                pd.cantidad AS cantidad_pedida,
                pd.unidad_venta,
                pd.seleccionada,
                pd.estado_produccion,
                COALESCE(NULLIF(pd.sector, ''), 'Por definir') AS sector,
                COALESCE(NULLIF(pd.sucursal, ''), NULLIF(ped.sucursal, ''), '') AS sucursal,
                COALESCE(pr.nombre, 'Producto no disponible') AS producto,
                COALESCE(NULLIF(ped.nombre_cliente, ''), u.nombre_comercio, u.nombre_completo) AS cliente,
                ped.estado,
                ped.observaciones,
                COALESCE(asignadas.cantidad_asignada, 0) AS cantidad_asignada
            FROM pedido_detalles pd
            INNER JOIN pedidos ped ON ped.id = pd.pedido_id
            INNER JOIN usuarios u ON u.id = ped.usuario_id
            LEFT JOIN productos pr ON pr.id = pd.producto_id
            LEFT JOIN (
                SELECT pedido_detalle_id, SUM(cantidad) AS cantidad_asignada
                FROM tandas_produccion_detalles
                GROUP BY pedido_detalle_id
            ) asignadas ON asignadas.pedido_detalle_id = pd.id
                        WHERE pd.pedido_id IN ($marcadores)
                            AND ped.estado <> 'cancelado'
            ORDER BY ped.fecha_recepcion ASC, pd.pedido_id ASC, pd.id ASC
        ";
                if ($estado !== '') {
                        $sql = str_replace("AND ped.estado <> 'cancelado'", 'AND ped.estado = ?', $sql);
                }
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $tipos = str_repeat('i', count($pedidoIds)) . ($estado !== '' ? 's' : '');
        $parametros = $pedidoIds;
        if ($estado !== '') {
            $parametros[] = $estado;
        }
        $referencias = [$tipos];
        foreach ($parametros as $indice => &$parametro) {
            $referencias[] = &$parametro;
        }
        unset($parametro);
        call_user_func_array([$stmt, 'bind_param'], $referencias);
        if (!$stmt->execute()) {
            $stmt->close();
            return [];
        }

        $lineas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        foreach ($lineas as &$linea) {
            $asignada = (float) $linea['cantidad_asignada'];
            $cantidadPedida = (float) $linea['cantidad_pedida'];
            $lineaHeredada = (int) $linea['seleccionada'] === 1 || $linea['estado_produccion'] !== 'pendiente';
            if ($lineaHeredada) {
                $linea['cantidad_asignada'] = $cantidadPedida;
            }
            $linea['cantidad_disponible'] = max(
                0,
                round($cantidadPedida - (float) $linea['cantidad_asignada'], 1)
            );
            if ($lineaHeredada) {
                $linea['cantidad_disponible'] = 0;
            }
        }
        unset($linea);

        return $lineas;
    }

    public function agruparLineas(array $lineas): array
    {
        $grupos = [];
        foreach ($lineas as $linea) {
            $productoId = (int) ($linea['producto_id'] ?? 0);
            $unidad = trim((string) ($linea['unidad_venta'] ?? ''));
            $sector = trim((string) ($linea['sector'] ?? 'Por definir')) ?: 'Por definir';
            if ($productoId <= 0 || $unidad === '') {
                continue;
            }

            $claveNatural = $productoId . "\0" . $unidad . "\0" . $sector;
            $clave = hash('sha256', $claveNatural);
            if (!isset($grupos[$clave])) {
                $grupos[$clave] = [
                    'clave' => $clave,
                    'producto_id' => $productoId,
                    'producto' => (string) ($linea['producto'] ?? $linea['producto_nombre'] ?? 'Producto no disponible'),
                    'unidad_venta' => $unidad,
                    'sector' => $sector,
                    'cantidad_pedida' => 0.0,
                    'cantidad_asignada' => 0.0,
                    'cantidad_disponible' => 0.0,
                    'cantidad_para_tanda' => 0.0,
                    'origenes' => []
                ];
            }

            $grupos[$clave]['cantidad_pedida'] += (float) ($linea['cantidad_pedida'] ?? $linea['cantidad'] ?? 0);
            $grupos[$clave]['cantidad_asignada'] += (float) ($linea['cantidad_asignada'] ?? 0);
            $grupos[$clave]['cantidad_disponible'] += (float) ($linea['cantidad_disponible'] ?? 0);
            $grupos[$clave]['cantidad_para_tanda'] += (float) ($linea['cantidad_seleccionada'] ?? $linea['cantidad_disponible'] ?? 0);
            $grupos[$clave]['origenes'][] = $linea;
        }

        foreach ($grupos as &$grupo) {
            foreach (['cantidad_pedida', 'cantidad_asignada', 'cantidad_disponible', 'cantidad_para_tanda'] as $campo) {
                $grupo[$campo] = round($grupo[$campo], 1);
            }
            $grupo['estado_asignacion'] = $grupo['cantidad_disponible'] <= 0
                ? 'completo'
                : ($grupo['cantidad_asignada'] > 0 ? 'parcial' : 'sin_asignar');
        }
        unset($grupo);

        uasort($grupos, static fn ($a, $b) => [$a['sector'], $a['producto'], $a['unidad_venta']] <=> [$b['sector'], $b['producto'], $b['unidad_venta']]);
        return array_values($grupos);
    }

    public function prepararSeleccionGrupos(array $lineas, array $clavesGrupo, array $cantidadesParciales = []): array
    {
        $grupos = $this->agruparLineas($lineas);
        $porClave = [];
        foreach ($grupos as $grupo) {
            $porClave[$grupo['clave']] = $grupo;
        }

        $seleccion = [];
        $errores = [];
        $clavesSeguras = [];
        foreach ($clavesGrupo as $claveEntrada) {
            if (!is_string($claveEntrada) || !preg_match('/^[a-f0-9]{64}$/', $claveEntrada)) {
                $errores[] = 'Uno de los grupos seleccionados no es válido.';
                continue;
            }
            $clavesSeguras[] = $claveEntrada;
        }

        foreach (array_unique($clavesSeguras) as $clave) {
            if (!isset($porClave[$clave])) {
                $errores[] = 'Uno de los grupos seleccionados ya no está disponible.';
                continue;
            }
            $grupo = $porClave[$clave];
            $parcialDefinido = false;
            foreach ($grupo['origenes'] as $origen) {
                $id = (int) $origen['detalle_id'];
                if (array_key_exists($id, $cantidadesParciales) && is_scalar($cantidadesParciales[$id]) && trim((string) $cantidadesParciales[$id]) !== '') {
                    $parcialDefinido = true;
                } elseif (array_key_exists($id, $cantidadesParciales) && !is_scalar($cantidadesParciales[$id])) {
                    $errores[] = 'Una cantidad parcial no es válida.';
                }
            }

            foreach ($grupo['origenes'] as $origen) {
                $id = (int) $origen['detalle_id'];
                $disponible = (float) ($origen['cantidad_disponible'] ?? 0);
                if ($disponible <= 0) {
                    continue;
                }
                if (!$parcialDefinido) {
                    $cantidad = $disponible;
                } elseif (array_key_exists($id, $cantidadesParciales) && is_scalar($cantidadesParciales[$id]) && trim((string) $cantidadesParciales[$id]) !== '') {
                    $cantidad = $this->normalizarCantidad($cantidadesParciales[$id]);
                    if ($cantidad === false || $cantidad > $disponible) {
                        $errores[] = 'La cantidad de ' . $grupo['producto'] . ' para el pedido #' . $origen['pedido_id'] . ' supera lo disponible.';
                        continue;
                    }
                } else {
                    $cantidad = 0.0;
                }

                if ($cantidad > 0) {
                    $seleccion[$id] = [
                        'cantidad' => $cantidad,
                        'modo' => $parcialDefinido ? 'parcial' : 'completa',
                        'sector' => $grupo['sector']
                    ];
                }
            }
        }

        if ($seleccion === [] && $errores === []) {
            $errores[] = 'Seleccioná al menos un producto con cantidad disponible.';
        }

        return ['seleccion' => $seleccion, 'errores' => array_values(array_unique($errores))];
    }

    public function revisarSeleccion(array $seleccion, array $pedidoIds, string $estado = 'pendiente'): array
    {
        $estado = $this->normalizarFiltroEstado($estado);
        $lineasPedido = $this->obtenerLineasPedidos($pedidoIds, $estado);
        $porId = [];
        foreach ($lineasPedido as $linea) {
            $porId[(int) $linea['detalle_id']] = $linea;
        }

        $lineas = [];
        $errores = [];
        foreach ($seleccion as $detalleId => $datos) {
            $detalleId = (int) $detalleId;
            $linea = $porId[$detalleId] ?? null;
            if (!$linea || !is_array($datos)) {
                $errores[] = 'Uno de los productos seleccionados no pertenece a los pedidos elegidos.';
                continue;
            }

            $modoEntrada = $datos['modo'] ?? 'parcial';
            if (!is_scalar($modoEntrada) || !in_array((string) $modoEntrada, ['completa', 'parcial'], true)) {
                $errores[] = 'El modo de asignación seleccionado no es válido.';
                continue;
            }
            $modoAsignacion = (string) $modoEntrada;
            if ($modoAsignacion === 'completa') {
                $cantidad = (float) $linea['cantidad_disponible'];
            } else {
                $cantidadIngresada = $datos['cantidad'] ?? '';
                if (!is_scalar($cantidadIngresada)) {
                    $errores[] = 'La cantidad indicada para ' . $linea['producto'] . ' no es válida.';
                    continue;
                }
                $cantidadIngresada = trim((string) $cantidadIngresada);
                if ($cantidadIngresada === '' || preg_match('/^0(?:[.,]0)?$/', $cantidadIngresada)) {
                    continue;
                }
                $cantidad = $this->normalizarCantidad($cantidadIngresada);
            }
            if ($cantidad === false || $cantidad <= 0 || $cantidad > (float) $linea['cantidad_disponible']) {
                $errores[] = 'La cantidad de ' . $linea['producto'] . ' para el pedido #' . $linea['pedido_id'] . ' no es válida o supera el remanente disponible.';
                continue;
            }

            $sectorEntrada = $linea['sector'];
            if (!is_scalar($sectorEntrada)) {
                $errores[] = 'El sector de ' . $linea['producto'] . ' no es válido.';
                continue;
            }
            $sector = trim((string) $sectorEntrada);
            if ($sector === '') {
                $sector = 'Por definir';
            }
            if (strlen($sector) > 100) {
                $errores[] = 'El sector de ' . $linea['producto'] . ' supera los 100 caracteres.';
                continue;
            }

            $linea['cantidad_seleccionada'] = $cantidad;
            $linea['modo_asignacion'] = $modoAsignacion;
            $linea['sector_seleccionado'] = $sector;
            $lineas[] = $linea;
        }

        if ($lineas === [] && $errores === []) {
            $errores[] = 'Seleccioná al menos un producto e indicá una cantidad mayor que cero.';
        }

        return ['lineas' => $lineas, 'errores' => array_values(array_unique($errores))];
    }

    public function crearTanda(array $seleccion, int $usuarioId, array $metadatos = []): int|false
    {
        $this->ultimoError = '';
        $metadatos = $this->normalizarMetadatos($metadatos);
        if ($metadatos === false) {
            $this->ultimoError = 'El título, los destinos o las observaciones superan los límites permitidos.';
            return false;
        }

        if ($usuarioId <= 0 || $seleccion === [] || !$this->conn->begin_transaction()) {
            $this->ultimoError = 'No se pudo iniciar la creación de la tanda.';
            return false;
        }

        try {
            $lineaIds = array_values(array_unique(array_filter(array_map('intval', array_keys($seleccion)), static fn ($id) => $id > 0)));
            if (count($lineaIds) !== count($seleccion)) {
                throw new RuntimeException('La selección contiene líneas no válidas.');
            }
            sort($lineaIds);
            $marcadores = implode(',', array_fill(0, count($lineaIds), '?'));

            $stmt = $this->conn->prepare("SELECT DISTINCT pedido_id FROM pedido_detalles WHERE id IN ($marcadores) ORDER BY pedido_id");
            if (!$stmt) {
                throw new RuntimeException('No se pudieron validar los pedidos de origen.');
            }
            $tipos = str_repeat('i', count($lineaIds));
            $stmt->bind_param($tipos, ...$lineaIds);
            if (!$stmt->execute()) {
                $stmt->close();
                throw new RuntimeException('No se pudieron validar los pedidos de origen.');
            }
            $pedidoIds = array_map('intval', array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'pedido_id'));
            $stmt->close();
            if ($pedidoIds === []) {
                throw new RuntimeException('No se encontró ninguna línea de pedido válida.');
            }

            sort($pedidoIds);
            $pedidoMarcadores = implode(',', array_fill(0, count($pedidoIds), '?'));
            $stmt = $this->conn->prepare("SELECT id, estado FROM pedidos WHERE id IN ($pedidoMarcadores) ORDER BY id FOR UPDATE");
            if (!$stmt) {
                throw new RuntimeException('No se pudieron bloquear los pedidos seleccionados.');
            }
            $pedidoTipos = str_repeat('i', count($pedidoIds));
            $stmt->bind_param($pedidoTipos, ...$pedidoIds);
            if (!$stmt->execute()) {
                $stmt->close();
                throw new RuntimeException('No se pudieron bloquear los pedidos seleccionados.');
            }
            $pedidosBloqueados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            if (count($pedidosBloqueados) !== count($pedidoIds)) {
                throw new RuntimeException('Uno de los pedidos ya no está disponible.');
            }
            foreach ($pedidosBloqueados as $pedido) {
                if (!in_array($pedido['estado'], self::ESTADOS_COMERCIALES, true)) {
                    throw new RuntimeException('El pedido ya no está disponible para planificar.');
                }
            }

            $stmt = $this->conn->prepare("
                SELECT pd.id, pd.pedido_id, pd.producto_id, pd.cantidad, pd.unidad_venta,
                      pd.seleccionada, pd.estado_produccion, pd.sector, pd.sucursal, pr.nombre AS producto,
                       ped.estado, ped.observaciones, ped.sucursal AS sucursal_pedido
                FROM pedido_detalles pd
                INNER JOIN pedidos ped ON ped.id = pd.pedido_id
                LEFT JOIN productos pr ON pr.id = pd.producto_id
                WHERE pd.id IN ($marcadores)
                ORDER BY pd.pedido_id, pd.id
                FOR UPDATE
            ");
            if (!$stmt) {
                throw new RuntimeException('No se pudieron validar las líneas de pedido.');
            }
            $stmt->bind_param($tipos, ...$lineaIds);
            if (!$stmt->execute()) {
                $stmt->close();
                throw new RuntimeException('No se pudieron validar las líneas de pedido.');
            }
            $lineasBloqueadas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            if (count($lineasBloqueadas) !== count($lineaIds)) {
                throw new RuntimeException('Una línea seleccionada ya no existe.');
            }

            $stmtAsignadas = $this->conn->prepare('SELECT COALESCE(SUM(cantidad), 0) AS total FROM tandas_produccion_detalles WHERE pedido_detalle_id = ?');
            if (!$stmtAsignadas) {
                throw new RuntimeException('No se pudieron validar las cantidades ya planificadas.');
            }

            $lineasValidadas = [];
            foreach ($lineasBloqueadas as $linea) {
                $id = (int) $linea['id'];
                $datos = $seleccion[$id] ?? $seleccion[(string) $id] ?? null;
                if (
                    !is_array($datos) ||
                    $linea['estado'] === 'cancelado' ||
                    (int) $linea['seleccionada'] === 1 ||
                    $linea['estado_produccion'] !== 'pendiente'
                ) {
                    throw new RuntimeException('El producto del pedido #' . $linea['pedido_id'] . ' ya fue enviado a producción o no está disponible.');
                }

                $stmtAsignadas->bind_param('i', $id);
                if (!$stmtAsignadas->execute()) {
                    throw new RuntimeException('No se pudieron validar las cantidades ya planificadas.');
                }
                $asignada = (float) $stmtAsignadas->get_result()->fetch_assoc()['total'];
                $disponible = round((float) $linea['cantidad'] - $asignada, 1);
                if ($disponible < 0) {
                    throw new RuntimeException('La cantidad ya asignada supera el pedido #' . $linea['pedido_id'] . '.');
                }
                $modoEntrada = $datos['modo'] ?? 'parcial';
                if (!is_scalar($modoEntrada) || !in_array((string) $modoEntrada, ['completa', 'parcial'], true)) {
                    throw new RuntimeException('El modo de asignación no es válido. Volvé a revisar los productos.');
                }
                $modoAsignacion = (string) $modoEntrada;
                $cantidad = $modoAsignacion === 'completa'
                    ? $disponible
                    : $this->normalizarCantidad($datos['cantidad'] ?? null);
                if ($cantidad === false || $cantidad <= 0) {
                    throw new RuntimeException('Indicá una cantidad válida para ' . ($linea['producto'] ?: 'el producto') . '.');
                }
                if ($cantidad > $disponible) {
                    throw new RuntimeException('La cantidad de ' . ($linea['producto'] ?: 'el producto') . ' supera el remanente disponible (' . number_format(max(0, $disponible), 1, ',', '') . ').');
                }

                $sectorEntrada = $linea['sector'] ?? '';
                if (!is_scalar($sectorEntrada)) {
                    throw new RuntimeException('El sector indicado para ' . ($linea['producto'] ?: 'el producto') . ' no es válido.');
                }
                $sector = trim((string) $sectorEntrada);
                $sector = $sector !== '' ? $sector : 'Por definir';
                if (strlen($sector) > 100) {
                    throw new RuntimeException('El nombre del sector no puede superar los 100 caracteres.');
                }

                $lineasValidadas[] = [
                    'detalle_id' => $id,
                    'pedido_id' => (int) $linea['pedido_id'],
                    'producto_id' => (int) $linea['producto_id'],
                    'producto' => (string) ($linea['producto'] ?: 'Producto no disponible'),
                    'cantidad' => $cantidad,
                    'unidad_venta' => (string) $linea['unidad_venta'],
                    'sector' => $sector,
                    'sucursal' => (string) ($linea['sucursal'] ?: $linea['sucursal_pedido'] ?: ''),
                    'observaciones' => (string) ($linea['observaciones'] ?? '')
                ];
            }
            $stmtAsignadas->close();

            if ($lineasValidadas === []) {
                throw new RuntimeException('La tanda debe contener al menos un producto.');
            }

            $destinosJson = json_encode($metadatos['destinos'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            $titulo = $metadatos['titulo'];
            $observaciones = $metadatos['observaciones'];
            $stmtTanda = $this->conn->prepare('INSERT INTO tandas_produccion (usuario_id, titulo, destinos, observaciones) VALUES (?, ?, ?, ?)');
            if (!$stmtTanda) {
                throw new RuntimeException('No se pudo crear la tanda de producción.');
            }
            $stmtTanda->bind_param('isss', $usuarioId, $titulo, $destinosJson, $observaciones);
            if (!$stmtTanda->execute()) {
                $stmtTanda->close();
                throw new RuntimeException('No se pudo crear la tanda de producción.');
            }
            $tandaId = (int) $this->conn->insert_id;
            $stmtTanda->close();

            if ($titulo === '') {
                $stmtTitulo = $this->conn->prepare("UPDATE tandas_produccion SET titulo = CONCAT('Tanda #', id, ' - ', DATE_FORMAT(fecha_creacion, '%d/%m/%Y %H:%i')) WHERE id = ?");
                if (!$stmtTitulo) {
                    throw new RuntimeException('No se pudo generar el título de la tanda.');
                }
                $stmtTitulo->bind_param('i', $tandaId);
                if (!$stmtTitulo->execute()) {
                    $stmtTitulo->close();
                    throw new RuntimeException('No se pudo generar el título de la tanda.');
                }
                $stmtTitulo->close();
            }

            $stmtDetalle = $this->conn->prepare('
                INSERT INTO tandas_produccion_detalles
                    (tanda_id, pedido_detalle_id, pedido_id, producto_id, producto_nombre, cantidad, unidad_venta, sector, sucursal, observaciones)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');
            if (!$stmtDetalle) {
                throw new RuntimeException('No se pudieron guardar los productos de la tanda.');
            }
            foreach ($lineasValidadas as $linea) {
                $stmtDetalle->bind_param(
                    'iiiisdssss',
                    $tandaId,
                    $linea['detalle_id'],
                    $linea['pedido_id'],
                    $linea['producto_id'],
                    $linea['producto'],
                    $linea['cantidad'],
                    $linea['unidad_venta'],
                    $linea['sector'],
                    $linea['sucursal'],
                    $linea['observaciones']
                );
                if (!$stmtDetalle->execute()) {
                    $stmtDetalle->close();
                    throw new RuntimeException('No se pudieron guardar los productos de la tanda.');
                }
            }
            $stmtDetalle->close();
            $this->conn->commit();
            return $tandaId;
        } catch (Throwable $e) {
            $this->conn->rollback();
            $this->ultimoError = $e->getMessage();
            error_log('Error creando tanda de producción: ' . $e->getMessage());
            return false;
        }
    }

    public function obtenerTandas(): array
    {
        $tituloSelect = $this->metadatosDisponibles() ? 't.titulo' : 'NULL AS titulo';
        $sql = '
            SELECT t.id, ' . $tituloSelect . ', t.fecha_creacion, u.nombre_completo AS responsable,
                   COUNT(td.id) AS cantidad_lineas,
                   COUNT(DISTINCT td.pedido_id) AS cantidad_pedidos
            FROM tandas_produccion t
            INNER JOIN usuarios u ON u.id = t.usuario_id
            LEFT JOIN tandas_produccion_detalles td ON td.tanda_id = t.id
            GROUP BY t.id
            ORDER BY t.fecha_creacion DESC, t.id DESC
        ';
        $resultado = $this->conn->query($sql);
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function obtenerTanda(int $tandaId): ?array
    {
        $metadatosSelect = $this->metadatosDisponibles()
            ? 't.titulo, t.destinos, t.observaciones'
            : 'NULL AS titulo, NULL AS destinos, NULL AS observaciones';
        $stmt = $this->conn->prepare('
            SELECT t.id, ' . $metadatosSelect . ', t.fecha_creacion, u.nombre_completo AS responsable
            FROM tandas_produccion t
            INNER JOIN usuarios u ON u.id = t.usuario_id
            WHERE t.id = ?
        ');
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('i', $tandaId);
        if (!$stmt->execute()) {
            $stmt->close();
            return null;
        }
        $tanda = $stmt->get_result()->fetch_assoc() ?: null;
        $stmt->close();
        if ($tanda) {
            $destinos = json_decode((string) ($tanda['destinos'] ?? '[]'), true);
            $tanda['destinos'] = is_array($destinos) ? $destinos : [];
        }
        return $tanda;
    }

    public function actualizarMetadatos(int $tandaId, int $usuarioId, array $entrada): bool
    {
        $this->ultimoError = '';
        $metadatos = $this->normalizarMetadatos($entrada);
        if ($tandaId <= 0 || $usuarioId <= 0 || $metadatos === false) {
            $this->ultimoError = 'Revisá el título, los destinos y las observaciones.';
            return false;
        }

        if (!$this->conn->begin_transaction()) {
            $this->ultimoError = 'No se pudo iniciar la actualización de la tanda.';
            return false;
        }

        try {
            $stmtActual = $this->conn->prepare("SELECT titulo, destinos, observaciones, historial_metadatos, fecha_creacion, DATE_FORMAT(fecha_creacion, '%d/%m/%Y %H:%i') AS fecha_titulo FROM tandas_produccion WHERE id = ? FOR UPDATE");
            if (!$stmtActual) {
                throw new RuntimeException('No se pudo consultar la tanda.');
            }
            $stmtActual->bind_param('i', $tandaId);
            if (!$stmtActual->execute()) {
                $stmtActual->close();
                throw new RuntimeException('No se pudo consultar la tanda.');
            }
            $actual = $stmtActual->get_result()->fetch_assoc();
            $stmtActual->close();
            if (!$actual) {
                throw new RuntimeException('No se encontró la tanda solicitada.');
            }

            $tituloAnterior = trim((string) ($actual['titulo'] ?? '')) ?: 'Tanda #' . $tandaId . ' - ' . date('d/m/Y H:i', strtotime($actual['fecha_creacion']));
            $destinosAnteriores = json_decode((string) ($actual['destinos'] ?? '[]'), true);
            $destinosAnteriores = is_array($destinosAnteriores) ? $destinosAnteriores : [];
            $destinosNuevos = json_encode($metadatos['destinos'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            $titulo = $metadatos['titulo'] !== ''
                ? $metadatos['titulo']
                : 'Tanda #' . $tandaId . ' - ' . $actual['fecha_titulo'];

            $stmtUsuario = $this->conn->prepare('SELECT nombre_completo FROM usuarios WHERE id = ?');
            if (!$stmtUsuario) {
                throw new RuntimeException('No se pudo identificar al responsable del cambio.');
            }
            $stmtUsuario->bind_param('i', $usuarioId);
            if (!$stmtUsuario->execute()) {
                $stmtUsuario->close();
                throw new RuntimeException('No se pudo identificar al responsable del cambio.');
            }
            $usuario = $stmtUsuario->get_result()->fetch_assoc();
            $stmtUsuario->close();
            if (!$usuario) {
                throw new RuntimeException('El usuario responsable no existe.');
            }

            $historial = json_decode((string) ($actual['historial_metadatos'] ?? '[]'), true);
            if (!is_array($historial)) {
                $historial = [];
            }
            $historial[] = [
                'fecha_cambio' => date('Y-m-d H:i:s'),
                'usuario_id' => $usuarioId,
                'responsable' => (string) $usuario['nombre_completo'],
                'titulo_anterior' => $tituloAnterior,
                'destinos_anteriores' => $destinosAnteriores,
                'observaciones_anteriores' => (string) ($actual['observaciones'] ?? ''),
                'titulo_nuevo' => $titulo,
                'destinos_nuevos' => $metadatos['destinos'],
                'observaciones_nuevas' => $metadatos['observaciones']
            ];
            $historialJson = json_encode($historial, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            $observaciones = $metadatos['observaciones'];
            $stmtUpdate = $this->conn->prepare('UPDATE tandas_produccion SET titulo = ?, destinos = ?, observaciones = ?, historial_metadatos = ? WHERE id = ?');
            if (!$stmtUpdate) {
                throw new RuntimeException('No se pudieron guardar los datos de la tanda.');
            }
            $stmtUpdate->bind_param('ssssi', $titulo, $destinosNuevos, $observaciones, $historialJson, $tandaId);
            if (!$stmtUpdate->execute()) {
                $stmtUpdate->close();
                throw new RuntimeException('No se pudieron guardar los datos de la tanda.');
            }
            $stmtUpdate->close();
            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            $this->conn->rollback();
            $this->ultimoError = $e->getMessage();
            error_log('Error actualizando metadatos de tanda: ' . $e->getMessage());
            return false;
        }
    }

    public function obtenerCambiosTanda(int $tandaId): array
    {
        if (!$this->metadatosDisponibles()) {
            return [];
        }
        $stmt = $this->conn->prepare('
            SELECT historial_metadatos
            FROM tandas_produccion
            WHERE id = ?
        ');
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param('i', $tandaId);
        if (!$stmt->execute()) {
            $stmt->close();
            return [];
        }
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $cambios = json_decode((string) ($fila['historial_metadatos'] ?? '[]'), true);
        if (!is_array($cambios)) {
            return [];
        }
        foreach ($cambios as &$cambio) {
            $cambio['destinos_anteriores'] = is_array($cambio['destinos_anteriores'] ?? null) ? $cambio['destinos_anteriores'] : [];
            $cambio['destinos_nuevos'] = is_array($cambio['destinos_nuevos'] ?? null) ? $cambio['destinos_nuevos'] : [];
        }
        unset($cambio);
        return array_reverse($cambios);
    }

    public function obtenerDetallesTanda(int $tandaId): array
    {
        $stmt = $this->conn->prepare('
            SELECT id, pedido_detalle_id, pedido_id, producto_id, producto_nombre,
                 cantidad, unidad_venta, sector,
                 sucursal AS sucursal_pedido,
                 observaciones AS observaciones_pedido
            FROM tandas_produccion_detalles
            WHERE tanda_id = ?
            ORDER BY sector, producto_nombre, pedido_id
        ');
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param('i', $tandaId);
        if (!$stmt->execute()) {
            $stmt->close();
            return [];
        }
        $detalles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $detalles;
    }

    private function normalizarCantidad($valor): float|false
    {
        if (!is_scalar($valor)) {
            return false;
        }
        $valor = str_replace(',', '.', trim((string) $valor));
        if (!preg_match('/^(?:0|[1-9]\d{0,8})(?:\.\d)?$/', $valor)) {
            return false;
        }
        return (float) $valor;
    }

    private function normalizarMetadatos(array $entrada): array|false
    {
        $titulo = $entrada['titulo'] ?? '';
        $observaciones = $entrada['observaciones'] ?? '';
        $destinos = $entrada['destinos'] ?? [];
        if (!is_scalar($titulo) || !is_scalar($observaciones) || !is_array($destinos)) {
            return false;
        }
        $titulo = trim((string) $titulo);
        $observaciones = trim((string) $observaciones);
        if (mb_strlen($titulo, 'UTF-8') > 150 || mb_strlen($observaciones, 'UTF-8') > 3000 || count($destinos) > 20) {
            return false;
        }

        $destinosLimpios = [];
        foreach ($destinos as $destino) {
            if (!is_scalar($destino)) {
                return false;
            }
            $destino = trim((string) $destino);
            if ($destino === '') {
                continue;
            }
            if (mb_strlen($destino, 'UTF-8') > 150) {
                return false;
            }
            $clave = mb_strtolower($destino, 'UTF-8');
            if (!isset($destinosLimpios[$clave])) {
                $destinosLimpios[$clave] = $destino;
            }
        }

        return [
            'titulo' => $titulo,
            'destinos' => array_values($destinosLimpios),
            'observaciones' => $observaciones
        ];
    }
}