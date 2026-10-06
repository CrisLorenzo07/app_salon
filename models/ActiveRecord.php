<?php

namespace Model;

class ActiveRecord
{
    protected static $db;
    protected static $table = '';
    protected static $columnsDB = [];

    protected static $alerts = [];

    public static function setDB($database)
    {
        self::$db = $database;
    }

    public static function beginTransaction(): void
    {
        if (!self::$db->begin_transaction()) {
            throw new \RuntimeException('No se pudo iniciar la transacción.');
        }
    }

    public static function commit(): void
    {
        if (!self::$db->commit()) {
            throw new \RuntimeException('No se pudo confirmar la transacción.');
        }
    }

    public static function rollback(): void
    {
        if (!self::$db->rollback()) {
            throw new \RuntimeException('No se pudo revertir la transacción.');
        }
    }

    public static function setAlert($type, $message)
    {
        static::$alerts[$type][] = $message;
    }

    public static function getAlerts()
    {
        return static::$alerts;
    }

    public function validate()
    {
        static::$alerts = [];
        return static::$alerts;
    }

    public static function querySQL($query)
    {
        $result = self::$db->query($query);

        $array = [];
        while ($record = $result->fetch_assoc()) {
            $array[] = static::createObject($record);
        }

        $result->free();

        return $array;
    }

    protected static function createObject($record): static
    {
        $object = new static;

        foreach ($record as $key => $value) {
            if (property_exists($object, $key)) {
                $object->$key = $value;
            }
        }

        return $object;
    }

    public function attributes()
    {
        $attributes = [];
        foreach (static::$columnsDB as $column) {
            if ($column === 'id')
                continue;
            $attributes[$column] = $this->$column;
        }
        return $attributes;
    }

    public function sync($args = [])
    {
        foreach ($args as $key => $value) {
            if (property_exists($this, $key) && !is_null($value)) {
                $this->$key = $value;
            }
        }
    }

    public function save()
    {
        $result = '';
        if (!is_null($this->id)) {
            $result = $this->update();
        } else {
            $result = $this->create();
        }
        return $result;
    }

    public static function all()
    {
        $query = "SELECT * FROM " . static::$table;
        $result = self::querySQL($query);
        return $result;
    }

    public static function find($id)
    {
        $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $id === false ? null : static::where('id', $id);
    }

    public static function get($limit)
    {
        $limit = filter_var($limit, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($limit === false) {
            throw new \InvalidArgumentException('Límite no válido');
        }
        $query = "SELECT * FROM " . static::$table . " LIMIT {$limit}";
        $result = self::querySQL($query);
        return array_shift($result);
    }

    public static function where($column, $value): ?static
    {
        if (!in_array($column, static::$columnsDB, true)) {
            throw new \InvalidArgumentException('Columna no válida');
        }

        $query = "SELECT * FROM `" . static::$table . "` WHERE `$column` = ? LIMIT 1";
        $stmt = self::$db->prepare($query);

        try {
            $stmt->bind_param('s', $value);
            $stmt->execute();
            $record = $stmt->get_result()->fetch_assoc();
        } finally {
            $stmt->close();
        }

        return $record ? static::createObject($record) : null;
    }

    public function create()
    {
        $attributes = $this->attributes();
        $columns = implode(', ', array_map(fn ($column) => "`{$column}`", array_keys($attributes)));
        $placeholders = implode(', ', array_fill(0, count($attributes), '?'));
        $stmt = self::$db->prepare('INSERT INTO `' . static::$table . '` (' . $columns . ') VALUES (' . $placeholders . ')');
        try {
            $values = array_values($attributes);
            $stmt->bind_param(str_repeat('s', count($values)), ...$values);
            $result = $stmt->execute();
            return ['result' => $result, 'id' => self::$db->insert_id];
        } finally {
            $stmt->close();
        }
    }

    public function update()
    {
        $attributes = $this->attributes();
        $assignments = implode(', ', array_map(fn ($column) => "`{$column}` = ?", array_keys($attributes)));
        $stmt = self::$db->prepare('UPDATE `' . static::$table . '` SET ' . $assignments . ' WHERE id = ? LIMIT 1');
        try {
            $values = [...array_values($attributes), $this->id];
            $stmt->bind_param(str_repeat('s', count($values)), ...$values);
            return $stmt->execute();
        } finally {
            $stmt->close();
        }
    }

    public function delete()
    {
        $stmt = self::$db->prepare('DELETE FROM `' . static::$table . '` WHERE id = ? LIMIT 1');
        try {
            $stmt->bind_param('i', $this->id);
            return $stmt->execute();
        } finally {
            $stmt->close();
        }
    }
}
