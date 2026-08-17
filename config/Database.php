<?php

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'don_diego';

$conn = new mysqli(
    $host,
    $user,
    $password,
    $database
);

if ($conn->connect_error) {

    error_log($conn->connect_error);

    exit('Error al conectar con el servidor.');

}

$conn->set_charset('utf8mb4');