<?php

class Usuario
{
    private $db;


    // CONSTRUCTOR
    public function __construct($db)
    {
        $this->db = $db;
    }


    // OBTENER USUARIO POR ID
    public function obtenerPorId($id)
    {
        $sql = "
            SELECT
                id,
                nombre_completo,
                nombre_comercio,
                email,
                telefono,
                direccion,
                password,
                rol,
                fecha_registro,
                email_verificado
            FROM usuarios
            WHERE id = ?
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            die("Error en obtenerPorId: "
                . $this->db->error);
        }

        $stmt->bind_param("i", $id);

        $stmt->execute();

        $resultado = $stmt->get_result();

        $usuario = $resultado->fetch_assoc();

        $stmt->close();

        return $usuario;
    }


    // OBTENER USUARIO POR EMAIL
    public function obtenerPorEmail($email)
    {
        $sql = "
            SELECT
                id,
                nombre_completo,
                nombre_comercio,
                email,
                telefono,
                direccion,
                password,
                rol,
                fecha_registro,
                email_verificado
            FROM usuarios
            WHERE email = ?
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            die("Error en obtenerPorEmail: "
                . $this->db->error);
        }

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $resultado = $stmt->get_result();

        $usuario = $resultado->fetch_assoc();

        $stmt->close();

        return $usuario;
    }


    // ACTUALIZAR DATOS DEL USUARIO
    public function actualizarDatos(
        $id,
        $nombreCompleto,
        $nombreComercio,
        $telefono,
        $direccion
    ) {

        $sql = "
            UPDATE usuarios
            SET
                nombre_completo = ?,
                nombre_comercio = ?,
                telefono = ?,
                direccion = ?
            WHERE id = ?
        ";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            die("Error en actualizarDatos: "
                . $this->db->error);
        }

        $stmt->bind_param(
            "ssssi",
            $nombreCompleto,
            $nombreComercio,
            $telefono,
            $direccion,
            $id
        );

        $resultado = $stmt->execute();

        $stmt->close();

        return $resultado;
    }


    // ACTUALIZAR DATOS DE CONTACTO
    public function actualizarContacto(
        $id,
        $telefono,
        $direccion
    ) {

        $sql = "
            UPDATE usuarios
            SET
                telefono = ?,
                direccion = ?
            WHERE id = ?
        ";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            die("Error en actualizarContacto: "
                . $this->db->error);
        }

        $stmt->bind_param(
            "ssi",
            $telefono,
            $direccion,
            $id
        );

        $resultado = $stmt->execute();

        $stmt->close();

        return $resultado;
    }


    // COMPROBAR SI EXISTE EMAIL
    public function existeEmail($email)
    {
        $sql = "
            SELECT id
            FROM usuarios
            WHERE email = ?
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            die("Error en existeEmail: "
                . $this->db->error);
        }

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $resultado = $stmt->get_result();

        $existe = $resultado->num_rows > 0;

        $stmt->close();

        return $existe;
    }


    // OBTENER TODOS LOS USUARIOS
    public function obtenerTodos()
    {
        $sql = "
            SELECT
                id,
                nombre_completo,
                nombre_comercio,
                email,
                telefono,
                direccion,
                rol,
                fecha_registro,
                email_verificado
            FROM usuarios
            ORDER BY id DESC
        ";

        $resultado = $this->db->query($sql);

        if (!$resultado) {
            die("Error en obtenerTodos: "
                . $this->db->error);
        }

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerClientes($busqueda = '', $rol = 'cliente')
    {
        $sql = "
            SELECT
                id,
                nombre_completo,
                nombre_comercio,
                email,
                telefono,
                rol,
                fecha_registro
            FROM usuarios
            WHERE rol IN ('cliente', 'empleado', 'admin')
        ";

        $parametroBusqueda = '%' . $busqueda . '%';
        $parametros = [];
        $tipos = '';

        if ($rol !== 'todos') {
            $sql .= ' AND rol = ?';
            $parametros[] = $rol;
            $tipos .= 's';
        }

        if ($busqueda !== '') {
            $sql .= "
                AND (
                    nombre_completo LIKE ?
                    OR email LIKE ?
                    OR telefono LIKE ?
                )
            ";
            $parametros[] = $parametroBusqueda;
            $parametros[] = $parametroBusqueda;
            $parametros[] = $parametroBusqueda;
            $tipos .= 'sss';
        }

        $sql .= ' ORDER BY nombre_completo ASC';

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return [];
        }

        if (!empty($parametros)) {
            $stmt->bind_param($tipos, ...$parametros);
        }

        if (!$stmt->execute()) {
            $stmt->close();
            return [];
        }

        $clientes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $clientes;
    }

    // ACTUALIZAR ROL
    public function actualizarRol($id, $rol)
    {
        $sql = "
            UPDATE usuarios
            SET rol = ?
            WHERE id = ?
        ";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            die("Error en actualizarRol: "
                . $this->db->error);
        }

        $stmt->bind_param(
            "si",
            $rol,
            $id
        );

        $resultado = $stmt->execute();

        $stmt->close();

        return $resultado;
    }

    
}
