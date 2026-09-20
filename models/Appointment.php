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

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->date = $args['date'] ?? '';
        $this->time = $args['time'] ?? '';
        $this->user_id = $args['user_id'] ?? null;
    }


}