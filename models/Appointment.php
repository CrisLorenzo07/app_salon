<?php

namespace Model;

class Appointment extends ActiveRecord
{
    protected static $table = 'appointments';
    protected static $columnsDB = ['id', 'date', 'time', 'user_id'];

    public ?int $id;
    public ?string $date;
    public ?string $time;
    public ?int $user_id;

    public static function deleteWithServices(int $id): bool
    {
        self::beginTransaction();
        try {
            $stmt = self::$db->prepare('DELETE FROM service_appointments WHERE appointment_id = ?');
            try {
                $stmt->bind_param('i', $id);
                $stmt->execute();
            } finally {
                $stmt->close();
            }
            $stmt = self::$db->prepare('DELETE FROM appointments WHERE id = ?');
            try {
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $deleted = $stmt->affected_rows === 1;
            } finally {
                $stmt->close();
            }
            if (!$deleted) {
                self::rollback();
                return false;
            }
            self::commit();
            return true;
        } catch (\Throwable $error) {
            self::rollback();
            throw $error;
        }
    }

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->date = $args['date'] ?? '';
        $this->time = $args['time'] ?? '';
        $this->user_id = $args['user_id'] ?? null;
    }


}