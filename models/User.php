<?php

namespace Model;

class User extends ActiveRecord
{
    protected static $table = 'users';
    protected static $columnsDB = ['id', 'name', 'last_name', 'phone', 'email', 'password', 'admin', 'confirmed', 'token', 'token_purpose', 'token_expires_at'];

    public ?int $id;
    public string $name;
    public string $last_name;
    public string $phone;
    public string $email;
    public string $password;
    public int $admin;
    public int $confirmed;
    public string $token;
    public ?string $token_purpose;
    public ?int $token_expires_at;

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
        $this->token_purpose = $args['token_purpose'] ?? null;
        $this->token_expires_at = $args['token_expires_at'] ?? null;
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

        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alerts['error'][] = 'Introduce un email válido';
        }
        foreach (['name' => ['Nombre', 60], 'last_name' => ['Apellido', 60], 'phone' => ['Teléfono', 10], 'email' => ['Email', 30]] as $field => [$label, $limit]) {
            if (mb_strlen($this->$field) > $limit) {
                self::$alerts['error'][] = "{$label} admite como máximo {$limit} caracteres";
            }
        }
        if (strlen($this->password) > 72) {
            self::$alerts['error'][] = 'La contraseña no puede superar 72 bytes';
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

    public function createToken(string $purpose = 'confirmation'): string
    {
        if (!in_array($purpose, ['confirmation', 'reset'], true)) {
            throw new \InvalidArgumentException('Propósito de token no válido');
        }
        $raw = bin2hex(random_bytes(32));
        $this->token = hash('sha256', $raw);
        $this->token_purpose = $purpose;
        $this->token_expires_at = time() + ($purpose === 'reset' ? 3600 : 86400);
        return $raw;
    }

    public static function tokenStorageReady(): bool
    {
        try {
            self::$db->query('SELECT token_purpose, token_expires_at FROM users LIMIT 0');
            return true;
        } catch (\mysqli_sql_exception $error) {
            error_log('Falta aplicar la migración 004 de tokens de cuenta.');
            return false;
        }
    }

    public static function forToken($raw, string $purpose): ?self
    {
        if (!is_string($raw) || !preg_match('/^[a-f0-9]{64}$/D', $raw)) {
            return null;
        }
        $user = self::where('token', hash('sha256', $raw));
        return $user && $user->token_purpose === $purpose && ($user->token_expires_at ?? 0) > time()
            ? $user : null;
    }

    public static function consumeToken(string $raw, string $purpose, ?string $passwordHash = null): bool
    {
        if (!preg_match('/^[a-f0-9]{64}$/D', $raw) || !in_array($purpose, ['confirmation', 'reset'], true)) {
            return false;
        }
        if ($purpose === 'reset' && $passwordHash === null) {
            return false;
        }
        $hash = hash('sha256', $raw);
        $now = time();
        $sql = $purpose === 'confirmation'
            ? "UPDATE users SET confirmed = 1, token = '', token_purpose = NULL, token_expires_at = NULL WHERE token = ? AND token_purpose = ? AND token_expires_at > ?"
            : "UPDATE users SET password = ?, token = '', token_purpose = NULL, token_expires_at = NULL WHERE token = ? AND token_purpose = ? AND token_expires_at > ? AND confirmed = 1";
        $stmt = self::$db->prepare($sql);
        try {
            if ($purpose === 'confirmation') {
                $stmt->bind_param('ssi', $hash, $purpose, $now);
            } else {
                $stmt->bind_param('sssi', $passwordHash, $hash, $purpose, $now);
            }
            $stmt->execute();
            return $stmt->affected_rows === 1;
        } finally {
            $stmt->close();
        }
    }

    public function validateLogin()
    {
        if (strlen($this->password) > 72) {
            self::$alerts['error'][] = 'La contraseña no puede superar 72 bytes';
        }
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
                'El email o la contraseña no son válidos, o la cuenta no está confirmada';
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
        if (strlen($this->password) > 72) {
            self::$alerts['error'][] = 'La contraseña no puede superar 72 bytes';
        }
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
