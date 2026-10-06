<?php

namespace Model;

class RequestLimit extends ActiveRecord
{
    public static function allowAttempt(string $scope): bool
    {
        if ($scope === '' || strlen($scope) > 64) {
            throw new \InvalidArgumentException('Ámbito de límite no válido');
        }
        self::beginTransaction();
        try {
            self::$db->query('DELETE FROM installation_attempts WHERE started_at < NOW() - INTERVAL 1 DAY');
            $stmt = self::$db->prepare('INSERT INTO installation_attempts (scope, attempts, started_at) VALUES (?, 0, NOW()) ON DUPLICATE KEY UPDATE scope = scope');
            $stmt->bind_param('s', $scope);
            $stmt->execute();
            $stmt->close();
            $stmt = self::$db->prepare('SELECT attempts, started_at <= NOW() - INTERVAL 15 MINUTE AS expired FROM installation_attempts WHERE scope = ? FOR UPDATE');
            $stmt->bind_param('s', $scope);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$row['expired'] && $row['attempts'] >= 10) {
                self::commit();
                return false;
            }
            $stmt = self::$db->prepare($row['expired']
                ? 'UPDATE installation_attempts SET attempts = 1, started_at = NOW() WHERE scope = ?'
                : 'UPDATE installation_attempts SET attempts = attempts + 1 WHERE scope = ?');
            $stmt->bind_param('s', $scope);
            $stmt->execute();
            $stmt->close();
            self::commit();
            return true;
        } catch (\Throwable $error) {
            self::rollback();
            throw $error;
        }
    }
}
