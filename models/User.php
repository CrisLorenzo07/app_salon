<?php

namespace Model;

class User extends ActiveRecord
{
    protected static $table = 'users';
    protected static $columnsDB = ['id', 'name', 'last_name', 'phone', 'email', 'password', 'admin', 'confirmed', 'token'];

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

    public function validateNewAccount()
    {
        if (!$this->name) {
            self::$alerts['error'][] = 'El nombre es obligatorio';
        }

        if (!$this->last_name) {
            self::$alerts['error'][] = 'El apellido es obligatorio';
        }

        if (!$this->email) {
            self::$alerts['error'][] = 'El email es obligatorio';
        }

        if (!$this->password) {
            self::$alerts['error'][] = 'El password es obligatorio';
        }

        if (
            strlen($this->password) < 8 ||
            !preg_match('/[A-Z]/', $this->password) ||
            !preg_match('/[a-z]/', $this->password) ||
            !preg_match('/[0-9]/', $this->password)
        ) {
            self::$alerts['error'][] = 'La contraseña debe tener al menos 8 caracteres e incluir mayúsculas, minúsculas y números';
        }

        return self::$alerts;
    }

    public function userExists()
    {
        $query = "SELECT * FROM " . self::$table . " WHERE email = ? LIMIT 1";
        $stmt = self::$db->prepare($query);

        try {
            $stmt->bind_param('s', $this->email);
            $stmt->execute();
            $result = $stmt->get_result();
        } finally {
            $stmt->close();
        }

        if ($result->num_rows) {
            self::$alerts['error'][] = 'El Usuario ya esta registrado';
        }

        return $result;
    }

    public function hashPassword()
    {
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    public function createToken()
    {
        $this->token = uniqid();
    }

    public function validateLogin()
    {
        if (!$this->email) {
            self::$alerts['error'][] = 'El email es obligatorio';
        }
        if (!$this->password) {
            self::$alerts['error'][] = 'El password es obligatorio';
        }

        return self::$alerts;
    }

    public function verifyPasswordAndConfirmation(string $password): bool
    {
        $result = password_verify($password, $this->password);
        if (!$result || !$this->confirmed) {
            self::$alerts['error'][] =
                'El password es incorrecto o tu cuenta no ha sido verificada';
            return false;
        }
        return true;
    }

    public function validateEmail()
    {
        if (!$this->email) {
            self::$alerts['error'][] = 'El email es obligatorio';
        }
        return self::$alerts;
    }

    public function validatePassword(string $confirmation): array
    {
        if ($this->password === '') {
            self::$alerts['error'][] = 'La contraseña es obligatoria';
        } elseif (
            strlen($this->password) < 8 ||
            !preg_match('/[A-Z]/', $this->password) ||
            !preg_match('/[a-z]/', $this->password) ||
            !preg_match('/[0-9]/', $this->password)
        ) {
            self::$alerts['error'][] =
                'La contraseña debe tener al menos 8 caracteres e incluir mayúsculas, minúsculas y números';
        }

        if ($confirmation === '') {
            self::$alerts['error'][] = 'Debes repetir la contraseña';
        } elseif ($this->password !== $confirmation) {
            self::$alerts['error'][] = 'Las contraseñas no coinciden';
        }

        return self::$alerts;
    }
}
