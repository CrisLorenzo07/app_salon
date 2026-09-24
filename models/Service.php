<?php

namespace Model;


class Service extends ActiveRecord
{
    protected static $table = 'services';
    protected static $columnsDB = ['id', 'name', 'price'];

    public ?int $id;
    public string $name;
    public float $price;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->name = $args['name'] ?? '';
        $this->price = $args['price'] ?? 0.0;
    }

    public function validate($args = null)
    {
        self::$alerts = [];
        $name = $args['name'] ?? $this->name;
        $price = $args === null ? $this->price : ($args['price'] ?? '');
        $this->name = is_string($name) ? trim($name) : '';
        $price = is_string($price) ? trim($price) : $price;

        if ($this->name === '') {
            self::$alerts['error'][] = 'El nombre del Servicio es obligatorio';
        }
        if ($price === '') {
            self::$alerts['error'][] = 'El precio del Servicio es obligatorio';
        }
        if (!is_numeric($price) || !is_finite((float) $price) || $price <= 0 || $price > 999.99) {
            self::$alerts['error'][] = 'El precio no es válido';
        } else {
            $this->price = (float) $price;
        }

        return self::$alerts;
    }

}
