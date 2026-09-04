<?php

class Producto
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }


    // =========================================================
    // OBTENER TODOS LOS PRODUCTOS
    // =========================================================

    public function obtenerTodos()
    {
        $sql = "
            SELECT
                p.id,
                p.nombre,
                p.descripcion,
                p.precio,
                p.stock,
                p.unidad_venta,
                p.imagen,
                p.activo,
                p.categoria_id,
                c.nombre AS categoria
            FROM productos p
            INNER JOIN categorias c
                ON p.categoria_id = c.id
            ORDER BY p.id DESC
        ";

        $resultado = $this->db->query($sql);

        if (!$resultado) {
            die("Error en obtenerTodos: " . $this->db->error);
        }

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }


    // =========================================================
    // OBTENER PRODUCTO POR ID
    // =========================================================

    public function obtenerPorId($id)
    {
        $sql = "
            SELECT
                p.id,
                p.nombre,
                p.descripcion,
                p.precio,
                p.stock,
                p.unidad_venta,
                p.imagen,
                p.activo,
                p.categoria_id,
                c.nombre AS categoria
            FROM productos p
            INNER JOIN categorias c
                ON p.categoria_id = c.id
            WHERE p.id = ?
        ";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            die("Error en obtenerPorId: " . $this->db->error);
        }

        $stmt->bind_param("i", $id);

        $stmt->execute();

        $resultado = $stmt->get_result();

        return $resultado->fetch_assoc();
    }


    // =========================================================
    // BUSCAR PRODUCTOS ACTIVOS
    // =========================================================

    public function buscar($busqueda)
    {
        $sql = "
            SELECT
                p.id,
                p.nombre,
                p.descripcion,
                p.precio,
                p.stock,
                p.unidad_venta,
                p.imagen,
                p.activo,
                c.nombre AS categoria
            FROM productos p
            INNER JOIN categorias c
                ON p.categoria_id = c.id
            WHERE p.activo = 1
            AND (
                p.nombre LIKE ?
                OR p.descripcion LIKE ?
            )
            ORDER BY p.id DESC
        ";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            die("Error en buscar: " . $this->db->error);
        }

        $busqueda = '%' . $busqueda . '%';

        $stmt->bind_param(
            "ss",
            $busqueda,
            $busqueda
        );

        $stmt->execute();

        $resultado = $stmt->get_result();

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }


    // =========================================================
    // OBTENER CATEGORÍAS
    // =========================================================

    public function obtenerCategorias()
    {
        $sql = "
            SELECT
                id,
                nombre
            FROM categorias
            ORDER BY nombre ASC
        ";

        $resultado = $this->db->query($sql);

        if (!$resultado) {
            die("Error en obtenerCategorias: " . $this->db->error);
        }

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }


    // =========================================================
    // OBTENER POR CATEGORÍA
    // =========================================================

    public function obtenerPorCategoria($categoriaId)
    {
        $sql = "
            SELECT
                p.id,
                p.nombre,
                p.descripcion,
                p.precio,
                p.stock,
                p.unidad_venta,
                p.imagen,
                p.activo,
                c.nombre AS categoria
            FROM productos p
            INNER JOIN categorias c
                ON p.categoria_id = c.id
            WHERE p.activo = 1
            AND p.categoria_id = ?
            ORDER BY p.id DESC
        ";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            die("Error en obtenerPorCategoria: " . $this->db->error);
        }

        $stmt->bind_param("i", $categoriaId);

        $stmt->execute();

        $resultado = $stmt->get_result();

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }


    // =========================================================
    // CREAR PRODUCTO
    // =========================================================

    public function crear(
        $nombre,
        $descripcion,
        $precio,
        $categoriaId,
        $imagen,
        $unidadVenta,
        $stock
    ) {
        if (!is_int($stock) || $stock < 0) {
            return false;
        }

        $sql = "
            INSERT INTO productos
            (
                nombre,
                descripcion,
                precio,
                categoria_id,
                imagen,
                unidad_venta,
                stock,
                activo
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, 1)
        ";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            die("Error en crear: " . $this->db->error);
        }

        $stmt->bind_param(
            "ssdissi",
            $nombre,
            $descripcion,
            $precio,
            $categoriaId,
            $imagen,
            $unidadVenta,
            $stock
        );

        return $stmt->execute();
    }


    // =========================================================
    // ACTUALIZAR PRODUCTO
    // =========================================================

    public function actualizar(
        $id,
        $nombre,
        $descripcion,
        $precio,
        $categoriaId,
        $imagen,
        $unidadVenta,
        $activo,
        $stock
    ) {
        if (!is_int($stock) || $stock < 0) {
            return false;
        }

        $sql = "
            UPDATE productos
            SET
                nombre = ?,
                descripcion = ?,
                precio = ?,
                categoria_id = ?,
                imagen = ?,
                unidad_venta = ?,
                activo = ?,
                stock = ?
            WHERE id = ?
        ";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            die("Error en actualizar: " . $this->db->error);
        }

        $stmt->bind_param(
            "ssdissiii",
            $nombre,
            $descripcion,
            $precio,
            $categoriaId,
            $imagen,
            $unidadVenta,
            $activo,
            $stock,
            $id
        );

        return $stmt->execute();
    }


    // =========================================================
    // DESACTIVAR PRODUCTO
    // =========================================================

    public function desactivar($id)
    {
        $sql = "
            UPDATE productos
            SET activo = 0
            WHERE id = ?
        ";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            die("Error en desactivar: " . $this->db->error);
        }

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }


    // =========================================================
    // ACTIVAR PRODUCTO
    // =========================================================

    public function activar($id)
    {
        $sql = "
            UPDATE productos
            SET activo = 1
            WHERE id = ?
        ";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            die("Error en activar: " . $this->db->error);
        }

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }


    // =========================================================
    // ELIMINAR PRODUCTO DEFINITIVAMENTE
    // =========================================================

    public function eliminar($id)
    {
        $sql = "
            DELETE FROM productos
            WHERE id = ?
        ";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            die("Error en eliminar: " . $this->db->error);
        }

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

    // =========================================================
    // OBTENER PRODUCTOS ACTIVOS PARA EL CATÁLOGO
    // =========================================================

    public function obtenerActivos()
    {
        $sql = "
        SELECT
            p.id,
            p.nombre,
            p.descripcion,
            p.precio,
            p.stock,
            p.unidad_venta,
            p.imagen,
            p.categoria_id,
            c.nombre AS categoria
        FROM productos p
        INNER JOIN categorias c
            ON p.categoria_id = c.id
        WHERE p.activo = 1
        ORDER BY p.id DESC
    ";

        $resultado = $this->db->query($sql);

        if (!$resultado) {
            die("Error en obtenerActivos: " . $this->db->error);
        }

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    // =========================================================
    // OBTENER NOVEDADES
    // =========================================================

    public function obtenerNovedades($limite = 3)
    {
        $limite = (int) $limite;

        if ($limite <= 0) {
            $limite = 3;
        }

        $sql = "
        SELECT
            id,
            nombre,
            imagen,
            stock,
            created_at
        FROM productos
        WHERE activo = 1
        ORDER BY created_at DESC, id DESC
        LIMIT $limite
    ";

        $resultado = $this->db->query($sql);

        if (!$resultado) {
            die('Error en obtenerNovedades: '
                . $this->db->error);
        }

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }
}
