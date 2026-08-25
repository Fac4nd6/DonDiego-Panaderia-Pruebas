<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Csrf.php';
require_once __DIR__ . '/../models/Usuario.php';


class UsuarioController
{

    /* =========================================================
       LOGIN
    ========================================================= */

    public function login($email, $password)
    {

        $email =
            strtolower(
                trim($email)
            );


        /* =====================================================
           VALIDAR DATOS
        ===================================================== */

        if (
            empty($email) ||
            empty($password)
        ) {

            return [
                'success' => false,
                'error' =>
                    'Por favor, completa todos los campos.'
            ];
        }


        if (
            !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {

            return [
                'success' => false,
                'error' =>
                    'El correo o la contraseña son incorrectos.'
            ];
        }


        /* =====================================================
           CONTROL BÁSICO DE INTENTOS
        ===================================================== */

        if (
            !isset(
                $_SESSION['login_intentos']
            )
        ) {

            $_SESSION['login_intentos'] = 0;
        }


        if (
            !isset(
                $_SESSION['login_ultimo_intento']
            )
        ) {

            $_SESSION['login_ultimo_intento'] = time();
        }


        $tiempoTranscurrido =
            time()
            - $_SESSION['login_ultimo_intento'];


        /*
         * Después de 10 minutos se reinician
         * los intentos.
         */

        if ($tiempoTranscurrido >= 600) {

            $_SESSION['login_intentos'] = 0;

            $_SESSION['login_ultimo_intento'] =
                time();
        }


        /*
         * Máximo 5 intentos dentro de la sesión
         * durante el período establecido.
         */

        if (
            $_SESSION['login_intentos'] >= 5
        ) {

            return [
                'success' => false,
                'error' =>
                    'Demasiados intentos fallidos. '
                    . 'Esperá unos minutos e intentá nuevamente.'
            ];
        }


        /* =====================================================
           BUSCAR USUARIO
        ===================================================== */

        global $conn;


        $usuarioModel =
            new Usuario($conn);


        $usuario =
            $usuarioModel->obtenerPorEmail(
                $email
            );


        /* =====================================================
           VERIFICAR CONTRASEÑA
        ===================================================== */

        if (
            !$usuario ||
            !password_verify(
                $password,
                $usuario['password']
            )
        ) {

            $_SESSION['login_intentos']++;

            $_SESSION['login_ultimo_intento'] =
                time();


            return [
                'success' => false,
                'error' =>
                    'El correo o la contraseña son incorrectos.'
            ];
        }


        /* =====================================================
           VERIFICAR EMAIL
        ===================================================== */

        if (
            (int) $usuario['email_verificado'] !== 1
        ) {

            return [
                'success' => false,
                'error' =>
                    'Debes verificar tu correo electrónico antes de iniciar sesión.'
            ];
        }


        /* =====================================================
           REGENERAR SESIÓN
        ===================================================== */

        session_regenerate_id(true);


        /* =====================================================
           GUARDAR DATOS DE SESIÓN
        ===================================================== */

        $_SESSION['usuario_id'] =
            (int) $usuario['id'];

        $_SESSION['usuario_nombre'] =
            $usuario['nombre_completo'];

        $_SESSION['usuario_email'] =
            $usuario['email'];

        $_SESSION['usuario_rol'] =
            $usuario['rol'];


        /* =====================================================
           REINICIAR INTENTOS
        ===================================================== */

        $_SESSION['login_intentos'] = 0;

        $_SESSION['login_ultimo_intento'] =
            time();


        /* =====================================================
           REGENERAR CSRF
        ===================================================== */

        $_SESSION['csrf_token'] =
            bin2hex(
                random_bytes(32)
            );


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


        $nombreCompleto =
            trim($nombreCompleto);

        $nombreComercio =
            trim($nombreComercio);

        $telefono =
            trim($telefono);

        $direccion =
            trim($direccion);


        /* =====================================================
           VALIDACIONES
        ===================================================== */

        if (
            empty($nombreCompleto)
        ) {

            return [
                'success' => false,
                'error' =>
                    'El nombre completo es obligatorio.'
            ];
        }


        if (
            strlen($nombreCompleto) > 100
        ) {

            return [
                'success' => false,
                'error' =>
                    'El nombre completo es demasiado largo.'
            ];
        }


        if (
            empty($nombreComercio)
        ) {

            return [
                'success' => false,
                'error' =>
                    'El nombre del comercio es obligatorio.'
            ];
        }


        if (
            strlen($nombreComercio) > 150
        ) {

            return [
                'success' => false,
                'error' =>
                    'El nombre del comercio es demasiado largo.'
            ];
        }


        if (
            empty($telefono)
        ) {

            return [
                'success' => false,
                'error' =>
                    'El teléfono es obligatorio.'
            ];
        }


        if (
            empty($direccion)
        ) {

            return [
                'success' => false,
                'error' =>
                    'La dirección es obligatoria.'
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
                'error' =>
                    'No se pudieron actualizar los datos.'
            ];
        }


        /* =====================================================
           ACTUALIZAR SESIÓN
        ===================================================== */

        $_SESSION['usuario_nombre'] =
            $nombreCompleto;


        return [
            'success' => true,
            'mensaje' =>
                'Tus datos fueron actualizados correctamente.'
        ];
    }


    /* =========================================================
       LOGOUT
    ========================================================= */

    public function logout()
    {

        if (
            session_status() === PHP_SESSION_NONE
        ) {

            session_start();
        }


        /* =====================================================
           ELIMINAR DATOS DE SESIÓN
        ===================================================== */

        $_SESSION = [];


        /* =====================================================
           ELIMINAR COOKIE DE SESIÓN
        ===================================================== */

        if (
            ini_get('session.use_cookies')
        ) {

            $params =
                session_get_cookie_params();


            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }


        /* =====================================================
           DESTRUIR SESIÓN
        ===================================================== */

        session_destroy();


        header(
            'Location: ../views/home/index.php'
        );

        exit;
    }
}