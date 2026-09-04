<?php

class Carrito
{
    // =========================================================
    // INICIALIZAR CARRITO
    // =========================================================

    public function inicializar()
    {
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }
    }


    // =========================================================
    // OBTENER CARRITO
    // =========================================================

    public function obtener()
    {
        $this->inicializar();

        return $_SESSION['carrito'];
    }


    // =========================================================
    // AGREGAR PRODUCTO
    // =========================================================

    public function agregar($producto, $cantidad = 1)
    {
        $this->inicializar();

        $id = (int) $producto['id'];

        $cantidad = (int) $cantidad;

        // Validar cantidad
        if ($cantidad < 1) {
            $cantidad = 1;
        }

        if ($cantidad > 99) {
            $cantidad = 99;
        }


        // -----------------------------------------------------
        // SI EL PRODUCTO YA EXISTE
        // -----------------------------------------------------

        if (isset($_SESSION['carrito'][$id])) {

            $_SESSION['carrito'][$id]['cantidad'] += $cantidad;


            // Máximo 99 unidades

            if (
                $_SESSION['carrito'][$id]['cantidad'] > 99
            ) {

                $_SESSION['carrito'][$id]['cantidad'] = 99;
            }

            return;
        }


        // -----------------------------------------------------
        // PRODUCTO NUEVO
        // -----------------------------------------------------

        $_SESSION['carrito'][$id] = [

            'id' => $id,

            'nombre' => $producto['nombre'],

            'descripcion' => $producto['descripcion'] ?? '',

            'precio' => (float) $producto['precio'],

            'imagen' => $producto['imagen'],

            'categoria' => $producto['categoria'] ?? '',

            'unidad_venta' => $producto['unidad_venta'] ?? 'unidad',

            'cantidad' => $cantidad
        ];
    }


    // =========================================================
    // ACTUALIZAR CANTIDAD
    // =========================================================

    public function actualizarCantidad($productoId, $cantidad)
    {
        $this->inicializar();

        $productoId = (int) $productoId;
        $cantidad = (int) $cantidad;


        if (!isset($_SESSION['carrito'][$productoId])) {
            return false;
        }


        // Si es 0 o menor, eliminar

        if ($cantidad <= 0) {

            unset($_SESSION['carrito'][$productoId]);

            return true;
        }


        // Máximo 99

        if ($cantidad > 99) {
            $cantidad = 99;
        }


        $_SESSION['carrito'][$productoId]['cantidad'] = $cantidad;

        return true;
    }


    // =========================================================
    // ELIMINAR PRODUCTO
    // =========================================================

    public function eliminar($productoId)
    {
        $this->inicializar();

        $productoId = (int) $productoId;


        if (!isset($_SESSION['carrito'][$productoId])) {
            return false;
        }


        unset($_SESSION['carrito'][$productoId]);

        return true;
    }


    // =========================================================
    // VACIAR CARRITO
    // =========================================================

    public function vaciar()
    {
        $_SESSION['carrito'] = [];
    }


    // =========================================================
    // CANTIDAD TOTAL DE PRODUCTOS
    // =========================================================

    public function cantidadProductos()
    {
        $this->inicializar();

        $cantidad = 0;


        foreach ($_SESSION['carrito'] as $item) {

            $cantidad += (int) $item['cantidad'];
        }


        return $cantidad;
    }


    // =========================================================
    // CALCULAR TOTAL
    // =========================================================

    public function calcularTotal()
    {
        $this->inicializar();

        $total = 0;


        foreach ($_SESSION['carrito'] as $item) {

            $total +=
                (float) $item['precio']
                * (int) $item['cantidad'];
        }


        return $total;
    }


    // =========================================================
    // CALCULAR SUBTOTAL
    // =========================================================

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
            * (int) $item['cantidad'];
    }


    // =========================================================
    // SABER SI ESTÁ VACÍO
    // =========================================================

    public function estaVacio()
    {
        $this->inicializar();

        return empty($_SESSION['carrito']);
    }
}