<?php

class Pedido
{
    private $conn;


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

        $stmt =
            $this->conn->prepare($sql);

        if (!$stmt) {
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

            $stmt->close();

            return false;
        }

        $pedidoId =
            $this->conn->insert_id;

        $stmt->close();

        return $pedidoId;
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

        $stmt =
            $this->conn->prepare($sql);

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

        $resultado =
            $stmt->get_result();

        $fila =
            $resultado->fetch_assoc();

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

        $stmt =
            $this->conn->prepare($sql);

        if (!$stmt) {
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

        $resultado =
            $stmt->execute();

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
                estado,
                total

            FROM pedidos

            WHERE usuario_id = ?

            ORDER BY fecha_pedido DESC
        ";

        $stmt =
            $this->conn->prepare($sql);

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

        $resultado =
            $stmt->get_result();

        $pedidos =
            $resultado->fetch_all(
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
                estado,
                total

            FROM pedidos

            WHERE id = ?

            AND usuario_id = ?

            LIMIT 1
        ";

        $stmt =
            $this->conn->prepare($sql);

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

        $resultado =
            $stmt->get_result();

        $pedido =
            $resultado->fetch_assoc();

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

        $stmt =
            $this->conn->prepare($sql);

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

        $resultado =
            $stmt->get_result();

        $pedido =
            $resultado->fetch_assoc();

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

        $stmt =
            $this->conn->prepare($sql);

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

        $resultado =
            $stmt->get_result();

        $detalles =
            $resultado->fetch_all(
                MYSQLI_ASSOC
            );

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

        $pedido =
            $this->obtenerPorId(
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

        $resultado =
            $this->conn->query($sql);

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

        $sql = "
            UPDATE pedidos

            SET estado = ?

            WHERE id = ?
        ";

        $stmt =
            $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "si",
            $estado,
            $pedidoId
        );

        $resultado =
            $stmt->execute();

        $stmt->close();

        return $resultado;
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

        $stmt =
            $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "ii",
            $pedidoId,
            $usuarioId
        );

        $resultado =
            $stmt->execute();

        $stmt->close();

        return $resultado;
    }
}