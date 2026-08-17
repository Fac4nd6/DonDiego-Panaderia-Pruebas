<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../config/Database.php';
require '../models/Productos.php';

echo "<h2>Conexión:</h2>";

if ($conn) {
    echo "✅ Conectado a la base de datos<br>";
} else {
    echo "❌ No conectado<br>";
}

echo "<h2>Productos:</h2>";

$producto = new Producto($conn);

$productos = $producto->obtenerTodos();

echo "<pre>";
print_r($productos);
echo "</pre>";

echo "<h2>Total:</h2>";

echo count($productos);