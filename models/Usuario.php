<?php

namespace Model;

class Usuario extends ActiveRecord
{
    protected static $tabla = 'users';
    protected static $columnasDB = ['id', 'name', 'last_name', 'phone', 'email', 'password', 'admin', 'confirmed', 'token'];

    public ?int $id;
    public string $name;
    public string $last_name;
    public string $phone;
    public string $email;
    public string $password;
    public int $admin;
    public int $confirmed;
    public string $token;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->name = $args['name'] ?? '';
        $this->last_name = $args['last_name'] ?? '';
        $this->phone = $args['phone'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->password = $args['password'] ?? '';
        $this->admin = $args['admin'] ?? 0;
        $this->confirmed = $args['confirmed'] ?? 0;
        $this->token = $args['token'] ?? '';
    }

    public function validarNuevaCuenta()
    {
        if (!$this->name) {
            self::$alertas['error'][] = 'El nombre es obligatorio';
        }

        if (!$this->last_name) {
            self::$alertas['error'][] = 'El apellido es obligatorio';
        }

        if (!$this->email) {
            self::$alertas['error'][] = 'El email es obligatorio';
        }

        if (!$this->password) {
            self::$alertas['error'][] = 'El password es obligatorio';
        }

        if (
            strlen($this->password) < 8 ||
            !preg_match('/[A-Z]/', $this->password) ||
            !preg_match('/[a-z]/', $this->password) ||
            !preg_match('/[0-9]/', $this->password)
        ) {
            self::$alertas['error'][] = 'La contraseña debe tener al menos 8 caracteres e incluir mayúsculas, minúsculas y números';
        }

        return self::$alertas;
    }

    public function existeUsuario()
    {
        $query = "SELECT * FROM " . self::$tabla . " WHERE email = '" . $this->email . "' LIMIT 1";
        $resultado = self::$db->query($query);

        if ($resultado->num_rows) {
            self::$alertas['error'][] = 'El Usuario ya esta registrado';
        }

        return $resultado;
    }

    public function hashPassword()
    {
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    public function crearToken()
    {
        $this->token = uniqid();
    }

    public function validarLogin()
    {
        if (!$this->email) {
            self::$alertas['error'][] = 'El email es obligatorio';
        }
        if (!$this->password) {
            self::$alertas['error'][] = 'El password es obligatorio';
        }

        return self::$alertas;
    }

    public function comprobarPasswordAndVerificado(string $password): bool
    {
        $resultado = password_verify($password, $this->password);
        if (!$resultado || !$this->confirmed) {
            self::$alertas['error'][] =
                'El password es incorrecto o tu cuenta no ha sido verificada';
            return false;
        }
        return true;
    }

    public function validarEmail()
    {
        if (!$this->email) {
            self::$alertas['error'][] = 'El email es obligatorio';
        }
        return self::$alertas;
    }

    public function validarPassword(string $confirmacion): array
    {
        if ($this->password === '') {
            self::$alertas['error'][] = 'La contraseña es obligatoria';
        } elseif (
            strlen($this->password) < 8 ||
            !preg_match('/[A-Z]/', $this->password) ||
            !preg_match('/[a-z]/', $this->password) ||
            !preg_match('/[0-9]/', $this->password)
        ) {
            self::$alertas['error'][] =
                'La contraseña debe tener al menos 8 caracteres e incluir mayúsculas, minúsculas y números';
        }

        if ($confirmacion === '') {
            self::$alertas['error'][] = 'Debes repetir la contraseña';
        } elseif ($this->password !== $confirmacion) {
            self::$alertas['error'][] = 'Las contraseñas no coinciden';
        }

        return self::$alertas;
    }
}
