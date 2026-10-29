<?php

class Carrito
{
    // INICIALIZAR CARRITO
    public function inicializar()
    {
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }
    }


    // OBTENER CARRITO
    public function obtener()
    {
        $this->inicializar();

        return $_SESSION['carrito'];
    }


    // AGREGAR PRODUCTO
    public function agregar($producto, $cantidad = 1, $unidadVenta = null)
    {
        $this->inicializar();

        $id = (int) $producto['id'];

        $presentaciones = Producto::obtenerPresentaciones($producto);
        if ($unidadVenta === null || !array_key_exists($unidadVenta, $presentaciones)) {
            $unidadVenta = array_key_first($presentaciones);
        }

        $clave = $id . '|' . $unidadVenta;
        $producto['unidad_venta'] = $unidadVenta;
        $producto['precio'] = $presentaciones[$unidadVenta];

        if (isset($_SESSION['carrito'][$id])) {
            $productoAnterior = $_SESSION['carrito'][$id];
            unset($_SESSION['carrito'][$id]);
            $claveAnterior = $id . '|' . ($productoAnterior['unidad_venta'] ?? 'unidad');
            $productoAnterior['clave'] = $claveAnterior;
            $_SESSION['carrito'][$claveAnterior] = $productoAnterior;
        }

        $cantidad = Producto::normalizarCantidad($cantidad, $unidadVenta);
        if ($cantidad === false) {
            return;
        }

        // Validar cantidad
        if ($cantidad < ($unidadVenta === 'kilogramo' ? 0.1 : 1)) {
            $cantidad = 1;
        }

        if ($cantidad > 99) {
            $cantidad = 99;
        }


        // SI EL PRODUCTO YA EXISTE
        if (isset($_SESSION['carrito'][$clave])) {

            $_SESSION['carrito'][$clave]['cantidad'] = round(
                (float) $_SESSION['carrito'][$clave]['cantidad'] + $cantidad,
                1
            );
            $_SESSION['carrito'][$clave]['precio'] = (float) $presentaciones[$unidadVenta];


            // Máximo 99 unidades

            if (
                $_SESSION['carrito'][$clave]['cantidad'] > 99
            ) {

                $_SESSION['carrito'][$clave]['cantidad'] = 99;
            }

            return;
        }


        // PRODUCTO NUEVO
        $_SESSION['carrito'][$clave] = [

            'clave' => $clave,

            'id' => $id,

            'nombre' => $producto['nombre'],

            'descripcion' => $producto['descripcion'] ?? '',

            'imagen' => $producto['imagen'],

            'categoria' => $producto['categoria'] ?? '',

            'unidad_venta' => $unidadVenta,

            'cantidad' => $cantidad,

            'precio' => (float) $presentaciones[$unidadVenta]
        ];
    }


    // ACTUALIZAR CANTIDAD
    public function actualizarCantidad($clave, $cantidad)
    {
        $this->inicializar();

        $clave = (string) $clave;
        if (!isset($_SESSION['carrito'][$clave])) {
            return false;
        }

        if (is_numeric($cantidad) && (float) $cantidad <= 0) {
            unset($_SESSION['carrito'][$clave]);
            return true;
        }

        $unidadVenta = $_SESSION['carrito'][$clave]['unidad_venta'] ?? 'unidad';
        $cantidad = Producto::normalizarCantidad($cantidad, $unidadVenta);
        if ($cantidad === false) {
            return false;
        }


        // Si es 0 o menor, eliminar

        if ($cantidad <= 0) {

            unset($_SESSION['carrito'][$clave]);

            return true;
        }


        // Máximo 99

        if ($cantidad > 99) {
            $cantidad = 99;
        }


        $_SESSION['carrito'][$clave]['cantidad'] = $cantidad;

        return true;
    }


    // ELIMINAR PRODUCTO
    public function eliminar($clave)
    {
        $this->inicializar();

        $clave = (string) $clave;


        if (!isset($_SESSION['carrito'][$clave])) {
            return false;
        }


        unset($_SESSION['carrito'][$clave]);

        return true;
    }


    // VACIAR CARRITO
    public function vaciar()
    {
        $_SESSION['carrito'] = [];
    }


    // CANTIDAD TOTAL DE PRODUCTOS
    public function cantidadProductos()
    {
        $this->inicializar();

        return count($_SESSION['carrito']);
    }


    // CALCULAR TOTAL
    public function calcularTotal()
    {
        $this->inicializar();

        $total = 0;


        foreach ($_SESSION['carrito'] as $item) {

            $total +=
                (float) $item['precio']
                * (float) $item['cantidad'];
        }


        return $total;
    }


    // CALCULAR SUBTOTAL
    public function calcularSubtotal($productoId)
    {
        $this->inicializar();

        $productoId = (int) $productoId;


        if (!isset($_SESSION['carrito'][$productoId])) {
            return 0;
        }


        $item = $_SESSION['carrito'][$productoId];


        return
            (float) $item['precio']
            * (float) $item['cantidad'];
    }


    // SABER SI ESTÁ VACÍO
    public function estaVacio()
    {
        $this->inicializar();

        return empty($_SESSION['carrito']);
    }
}