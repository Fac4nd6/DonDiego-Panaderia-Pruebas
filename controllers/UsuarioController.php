<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Usuario.php';


class UsuarioController
{

    /* =========================================================
       LOGIN
    ========================================================= */

    public function login($email, $password)
    {
        $email = trim($email);

        if (empty($email) || empty($password)) {

            return [
                'success' => false,
                'error' => 'Por favor, completa todos los campos.'
            ];
        }


        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            return [
                'success' => false,
                'error' => 'Ingresa un correo electrónico válido.'
            ];
        }


        global $conn;


        $usuarioModel = new Usuario($conn);

        $usuario = $usuarioModel->obtenerPorEmail($email);


        if (
            !$usuario ||
            !password_verify(
                $password,
                $usuario['password']
            )
        ) {

            return [
                'success' => false,
                'error' => 'El correo o la contraseña son incorrectos.'
            ];
        }


        /* =====================================================
           SESIÓN
        ===================================================== */

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }


        session_regenerate_id(true);


        $_SESSION['usuario_id'] =
            $usuario['id'];

        $_SESSION['usuario_nombre'] =
            $usuario['nombre_completo'];

        $_SESSION['usuario_email'] =
            $usuario['email'];

        $_SESSION['usuario_rol'] =
            $usuario['rol'];


        return [
            'success' => true
        ];
    }


    /* =========================================================
       ACTUALIZAR DATOS
    ========================================================= */

    public function actualizarDatos(
        $id,
        $nombreCompleto,
        $nombreComercio,
        $telefono,
        $direccion
    ) {

        global $conn;


        $nombreCompleto = trim($nombreCompleto);
        $nombreComercio = trim($nombreComercio);
        $telefono = trim($telefono);
        $direccion = trim($direccion);


        /* =====================================================
           VALIDACIONES
        ===================================================== */

        if (empty($nombreCompleto)) {

            return [
                'success' => false,
                'error' => 'El nombre completo es obligatorio.'
            ];
        }


        if (empty($nombreComercio)) {

            return [
                'success' => false,
                'error' => 'El nombre del comercio es obligatorio.'
            ];
        }


        if (empty($telefono)) {

            return [
                'success' => false,
                'error' => 'El teléfono es obligatorio.'
            ];
        }


        if (empty($direccion)) {

            return [
                'success' => false,
                'error' => 'La dirección es obligatoria.'
            ];
        }


        /* =====================================================
           ACTUALIZAR
        ===================================================== */

        $usuarioModel =
            new Usuario($conn);


        $resultado =
            $usuarioModel->actualizarDatos(
                $id,
                $nombreCompleto,
                $nombreComercio,
                $telefono,
                $direccion
            );


        if (!$resultado) {

            return [
                'success' => false,
                'error' => 'No se pudieron actualizar los datos.'
            ];
        }


        /* =====================================================
           ACTUALIZAR SESIÓN
        ===================================================== */

        $_SESSION['usuario_nombre'] =
            $nombreCompleto;


        return [
            'success' => true,
            'mensaje' => 'Tus datos fueron actualizados correctamente.'
        ];
    }


    /* =========================================================
       LOGOUT
    ========================================================= */

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }


        $_SESSION = [];

        session_destroy();


        header(
            'Location: ../views/home/index.php'
        );

        exit;
    }
}