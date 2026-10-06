<?php

namespace Model;

class Installation extends ActiveRecord
{
    public static function ownerEmail(): string
    {
        return trim($_ENV['INSTALLATION_OWNER_EMAIL'] ?? 'admin@admin.com');
    }

    public static function available(): bool
    {
        if (strtolower(self::ownerEmail()) === 'admin@admin.com'
            && !in_array($_ENV['APP_ENV'] ?? 'production', ['development', 'test'], true)) {
            return false;
        }
        if (!filter_var(self::ownerEmail(), FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        try {
            $row = self::$db->query('SELECT completed_at FROM installation_state WHERE id = 1')->fetch_assoc();
            self::$db->query('SELECT scope FROM installation_attempts LIMIT 0');
            self::$db->query('SELECT token_hash FROM installation_invitations LIMIT 0');
            if (!User::tokenStorageReady()) {
                return false;
            }
            $engines = self::$db->query("SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('users', 'installation_state', 'installation_attempts', 'installation_invitations')")->fetch_all(MYSQLI_ASSOC);
            return $row && $row['completed_at'] === null && count($engines) === 4
                && count(array_filter($engines, fn($table) => strtoupper($table['ENGINE']) === 'INNODB')) === 4;
        } catch (\mysqli_sql_exception $error) {
            error_log('Configuración inicial no disponible: ' . $error->getMessage());
            return false;
        }
    }

    public static function issueInvitation(): string
    {
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
        $email = self::ownerEmail();
        self::beginTransaction();
        try {
            $row = self::$db->query('SELECT completed_at FROM installation_state WHERE id = 1 FOR UPDATE')->fetch_assoc();
            if (!$row || $row['completed_at'] !== null) {
                throw new \RuntimeException('La instalación está cerrada.');
            }
            self::$db->query('DELETE FROM installation_invitations WHERE expires_at <= NOW()');
            $stmt = self::$db->prepare('INSERT INTO installation_invitations (token_hash, owner_email, expires_at) VALUES (?, ?, NOW() + INTERVAL 24 HOUR)');
            $stmt->bind_param('ss', $hash, $email);
            $stmt->execute();
            $stmt->close();
            self::commit();
            return $token;
        } catch (\Throwable $error) {
            self::rollback();
            throw $error;
        }
    }

    public static function validInvitation(string $hash): bool
    {
        if (!preg_match('/^[a-f0-9]{64}$/D', $hash)) {
            return false;
        }
        $email = self::ownerEmail();
        $stmt = self::$db->prepare('SELECT token_hash FROM installation_invitations WHERE token_hash = ? AND owner_email = ? AND expires_at > NOW()');
        try {
            $stmt->bind_param('ss', $hash, $email);
            $stmt->execute();
            return $stmt->get_result()->num_rows === 1;
        } finally {
            $stmt->close();
        }
    }

    public static function createAdministrator(User $user, string $invitationHash): void
    {
        self::beginTransaction();
        $transactionOpen = true;
        try {
            $row = self::$db->query('SELECT completed_at FROM installation_state WHERE id = 1 FOR UPDATE')->fetch_assoc();
            if (!$row || $row['completed_at'] !== null) {
                throw new \RuntimeException('La configuración inicial ya no está disponible.');
            }
            if (!self::validInvitation($invitationHash)) {
                throw new \RuntimeException('El enlace ha caducado o no es válido.');
            }
            $user->email = self::ownerEmail();
            if (self::$db->query('SELECT id FROM users WHERE admin = 1 LIMIT 1')->num_rows) {
                // Mantener el cierre aunque exista un administrador añadido por otro medio.
                self::$db->query('UPDATE installation_state SET completed_at = NOW() WHERE id = 1');
                self::commit();
                $transactionOpen = false;
                throw new \RuntimeException('La instalación ya tiene un administrador.');
            }
            if ($user->userExists()->num_rows) {
                throw new \RuntimeException('El email ya está registrado.');
            }
            $user->admin = 1;
            // La invitación válida ya acredita el acceso al correo del Administrador.
            $user->confirmed = 1;
            $user->hashPassword();
            $user->token = '';
            $result = $user->save();
            if (empty($result['result'])) {
                throw new \RuntimeException('No se pudo crear el administrador.');
            }
            self::$db->query('UPDATE installation_state SET completed_at = NOW() WHERE id = 1');
            self::$db->query('DELETE FROM installation_invitations');
            self::commit();
            $transactionOpen = false;
        } catch (\Throwable $error) {
            if ($transactionOpen) {
                self::rollback();
            }
            throw $error;
        }
    }
}
