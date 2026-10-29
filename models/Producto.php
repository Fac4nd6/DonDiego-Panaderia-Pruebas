<?php

class Producto
{
    private $db;

    public static function obtenerPresentaciones($producto)
    {
        $permitidas = ['unidad', 'docena', 'media docena', 'kilogramo'];
        $presentaciones = json_decode($producto['presentaciones'] ?? '', true);

        if (!is_array($presentaciones) || empty($presentaciones)) {
            $unidad = $producto['unidad_venta'] ?? 'unidad';
            $precio = $producto['precio'] ?? 0;
            return in_array($unidad, $permitidas, true)
                ? [$unidad => (float) $precio]
                : ['unidad' => (float) $precio];
        }

        $resultado = [];
        foreach ($presentaciones as $unidad => $precio) {
            if (in_array($unidad, $permitidas, true) && is_numeric($precio) && (float) $precio >= 0) {
                $resultado[$unidad] = (float) $precio;
            }
        }

        return $resultado;
    }

    public static function nombrePresentacion($unidad, $cantidad = 1)
    {
        $nombres = [
            'unidad' => ['unidad', 'unidades'],
            'docena' => ['docena', 'docenas'],
            'media docena' => ['media docena', 'medias docenas'],
            'kilogramo' => ['kilo', 'kilos']
        ];
        $opciones = $nombres[$unidad] ?? [$unidad, $unidad];

        return (float) $cantidad === 1.0 ? $opciones[0] : $opciones[1];
    }

    public static function obtenerStocks($producto)
    {
        $stocks = json_decode($producto['stocks_modalidades'] ?? '', true);

        if (!is_array($stocks) || empty($stocks)) {
            $unidad = $producto['unidad_venta'] ?? 'unidad';
            $stockAnterior = max(0, (int) ($producto['stock'] ?? 0));

            if ($unidad === 'kilogramo') {
                return ['unidad' => 0, 'kilogramo' => (float) $stockAnterior];
            }

            return [
                'unidad' => $stockAnterior * self::factorPiezas($unidad),
                'kilogramo' => 0.0
            ];
        }

        $tieneStocksPorModalidad = array_key_exists('docena', $stocks)
            || array_key_exists('media docena', $stocks);

        if ($tieneStocksPorModalidad) {
            $piezas = max(0, (int) ($stocks['unidad'] ?? 0))
                + max(0, (int) ($stocks['docena'] ?? 0)) * 12
                + max(0, (int) ($stocks['media docena'] ?? 0)) * 6;
        } else {
            $piezas = max(0, (int) ($stocks['unidad'] ?? 0));
        }

        $kilos = is_numeric($stocks['kilogramo'] ?? null)
            ? max(0, round((float) $stocks['kilogramo'], 1))
            : 0.0;

        return ['unidad' => $piezas, 'kilogramo' => $kilos];
    }

    public static function factorPiezas($unidad)
    {
        return match ($unidad) {
            'unidad' => 1,
            'media docena' => 6,
            'docena' => 12,
            default => 1
        };
    }

    public static function normalizarCantidad($cantidad, $unidad)
    {
        if ($unidad === 'kilogramo') {
            if (
                !is_numeric($cantidad) ||
                !preg_match('/^\d+(?:\.\d)?$/', (string) $cantidad)
            ) {
                return false;
            }

            $cantidad = round((float) $cantidad, 1);
            return $cantidad > 0 && $cantidad <= 99 ? $cantidad : false;
        }

        if (!is_numeric($cantidad)) {
            return false;
        }

        $cantidadNumerica = (float) $cantidad;
        if (
            $cantidadNumerica !== floor($cantidadNumerica) ||
            $cantidadNumerica <= 0 ||
            $cantidadNumerica > 99
        ) {
            return false;
        }

        return (int) $cantidadNumerica;
    }

    public static function normalizarStock($stock, $base)
    {
        if ($base === 'kilogramo') {
            if (
                !is_numeric($stock) ||
                !preg_match('/^\d+(?:\.\d)?$/', (string) $stock)
            ) {
                return false;
            }

            $stock = round((float) $stock, 1);
            return $stock >= 0 && $stock <= 2147483647 ? $stock : false;
        }

        $stock = filter_var($stock, FILTER_VALIDATE_INT);
        return $stock !== false && $stock >= 0 && $stock <= 2147483647
            ? $stock
            : false;
    }

    public static function consumoInventario($unidad, $cantidad)
    {
        if ($unidad === 'kilogramo') {
            return ['base' => 'kilogramo', 'cantidad' => round((float) $cantidad, 1)];
        }

        return [
            'base' => 'unidad',
            'cantidad' => (int) $cantidad * self::factorPiezas($unidad)
        ];
    }

    public static function stockDisponibleVenta($stocks, $unidad)
    {
        if ($unidad === 'kilogramo') {
            return round((float) ($stocks['kilogramo'] ?? 0), 1);
        }

        return (int) floor(
            (int) ($stocks['unidad'] ?? 0) / self::factorPiezas($unidad)
        );
    }

    public static function tieneStock($producto)
    {
        $stocks = self::obtenerStocks($producto);
        foreach (self::obtenerPresentaciones($producto) as $unidad => $_precio) {
            if (self::stockDisponibleVenta($stocks, $unidad) > 0) {
                return true;
            }
        }

        return false;
    }

    public static function formatearCantidad($cantidad, $unidad)
    {
        if ($unidad !== 'kilogramo') {
            return number_format((int) $cantidad, 0, ',', '.');
        }

        $valor = rtrim(rtrim(number_format((float) $cantidad, 1, ',', '.'), '0'), ',');
        return $valor;
    }

    public static function stockCompatibilidad($stocks)
    {
        return max(
            (int) ($stocks['unidad'] ?? 0),
            (int) ceil((float) ($stocks['kilogramo'] ?? 0))
        );
    }

    public function __construct($db)
    {
        $this->db = $db;
    }


    // OBTENER TODOS LOS PRODUCTOS
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
                p.presentaciones,
                p.stocks_modalidades,
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


    // OBTENER PRODUCTO POR ID
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
                p.presentaciones,
                p.stocks_modalidades,
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


    // BUSCAR PRODUCTOS ACTIVOS
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
                p.presentaciones,
                p.stocks_modalidades,
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


    // OBTENER CATEGORÍAS
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


    // OBTENER POR CATEGORÍA
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
                p.presentaciones,
                p.stocks_modalidades,
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


    // CREAR PRODUCTO
    public function crear(
        $nombre,
        $descripcion,
        $precio,
        $categoriaId,
        $imagen,
        $unidadVenta,
        $stock,
        $presentaciones,
        $stocksModalidades
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
                presentaciones,
                stocks_modalidades,
                stock,
                activo
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            die("Error en crear: " . $this->db->error);
        }

        $stmt->bind_param(
            "ssdissssi",
            $nombre,
            $descripcion,
            $precio,
            $categoriaId,
            $imagen,
            $unidadVenta,
            $presentaciones,
            $stocksModalidades,
            $stock
        );

        return $stmt->execute();
    }


    // ACTUALIZAR PRODUCTO
    public function actualizar(
        $id,
        $nombre,
        $descripcion,
        $precio,
        $categoriaId,
        $imagen,
        $unidadVenta,
        $activo,
        $stock,
        $presentaciones,
        $stocksModalidades
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
                presentaciones = ?,
                stocks_modalidades = ?,
                activo = ?,
                stock = ?
            WHERE id = ?
        ";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            die("Error en actualizar: " . $this->db->error);
        }

        $stmt->bind_param(
            "ssdissssiii",
            $nombre,
            $descripcion,
            $precio,
            $categoriaId,
            $imagen,
            $unidadVenta,
            $presentaciones,
            $stocksModalidades,
            $activo,
            $stock,
            $id
        );

        return $stmt->execute();
    }


    // DESACTIVAR PRODUCTO
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


    // ACTIVAR PRODUCTO
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


    // ELIMINAR PRODUCTO DEFINITIVAMENTE
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

    // OBTENER PRODUCTOS ACTIVOS PARA EL CATÁLOGO
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
            p.presentaciones,
            p.stocks_modalidades,
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

    // OBTENER NOVEDADES
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
