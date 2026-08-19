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
        $total
    ) {

        $sql = "
            INSERT INTO pedidos
            (
                usuario_id,
                fecha_recepcion,
                franja_horaria,
                direccion_entrega,
                estado,
                total
            )
            VALUES
            (
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
            return false;
        }

        $stmt->bind_param(
            "isssd",
            $usuarioId,
            $fechaRecepcion,
            $franjaHoraria,
            $direccionEntrega,
            $total
        );

        if (!$stmt->execute()) {
            $stmt->close();

            return false;
        }

        $pedidoId = $this->conn->insert_id;

        $stmt->close();

        return $pedidoId;
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
            estado,
            total
        FROM pedidos
        WHERE usuario_id = ?
        ORDER BY fecha_pedido DESC
    ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {

            die("ERROR EN obtenerPorUsuario(): "
                . $this->conn->error);
        }

        if (!$stmt->bind_param("i", $usuarioId)) {

            die("ERROR EN bind_param(): "
                . $stmt->error);
        }

        if (!$stmt->execute()) {

            die("ERROR EN execute(): "
                . $stmt->error);
        }

        $resultado = $stmt->get_result();

        if (!$resultado) {

            die("ERROR EN get_result(): "
                . $stmt->error);
        }

        $pedidos = $resultado->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $pedidos;
    }


    /* =========================================================
       OBTENER PEDIDO POR ID
       Y COMPROBAR QUE PERTENECE AL USUARIO
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

        $stmt->execute();

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

        $stmt->execute();

        $resultado = $stmt->get_result();

        $detalles = $resultado->fetch_all(
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
                p.estado,
                p.total,

                u.nombre_completo,
                u.email

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

        $sql = "
            UPDATE pedidos

            SET estado = ?

            WHERE id = ?
        ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "si",
            $estado,
            $pedidoId
        );

        $resultado = $stmt->execute();

        $stmt->close();

        return $resultado;
    }
}
