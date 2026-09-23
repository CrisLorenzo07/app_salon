<?php

namespace Model;

class AdminAppointment extends ActiveRecord
{
    protected static $table = 'service_appointments';
    protected static $columnsDB = ['id', 'date', 'time', 'client', 'email', 'phone', 'service', 'price'];

    public ?int $id;
    public string $date;
    public string $time;
    public string $client;
    public string $email;
    public string $phone;
    public string $service;
    public float $price;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->date = $args['date'] ?? '';
        $this->time = $args['time'] ?? '';
        $this->client = $args['client'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->phone = $args['phone'] ?? '';
        $this->service = $args['service'] ?? '';
        $this->price = $args['price'] ?? 0.0;
    }
}
