<?php

namespace Model;

class Service extends ActiveRecord
{
    protected static $table = 'services';
    protected static $columns = ['id', 'name', 'price'];

    public ?int $id;
    public string $name;
    public float $price;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->name = $args['name'] ?? '';
        $this->price = $args['price'] ?? 0.0;
    }

}