<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Producto.php';

class HomeController
{
    private $productoModel;

    public function __construct($db)
    {
        $this->productoModel = new Producto($db);
    }

    public function index()
    {
        // Obtener productos activos
        $productos = $this->productoModel->obtenerActivos();

        // Primeros 6 productos para la sección principal
        $productosDestacados = array_slice(
            $productos,
            0,
            6
        );

        // Productos recomendados aleatorios
        $productosRecomendados = $productos;

        shuffle($productosRecomendados);

        $productosRecomendados = array_slice(
            $productosRecomendados,
            0,
            3
        );

        require __DIR__ . '/../views/home/index.php';
    }
}

$homeController = new HomeController($conn);
$homeController->index();