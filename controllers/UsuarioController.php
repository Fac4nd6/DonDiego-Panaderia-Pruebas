<?php

require_once __DIR__ . '/../config/Database.php';

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

        $stmt = $conn->prepare("
            SELECT id, nombre_completo, email, password, rol
            FROM usuarios
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $result = $stmt->get_result();

        $usuario = $result->fetch_assoc();

        $stmt->close();


        if (!$usuario || !password_verify($password, $usuario['password'])) {
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

        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre_completo'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['usuario_rol'] = $usuario['rol'];


        return [
            'success' => true
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