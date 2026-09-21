<?php

namespace Model;

class ServiceAppointment extends ActiveRecord
{
    protected static $table = 'service_appointments';
    protected static $columnsDB = ['id', 'service_id', 'appointment_id'];

    public ?int $id;
    public ?int $service_id;
    public ?int $appointment_id;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->service_id = $args['service_id'] ?? null;
        $this->appointment_id = $args['appointment_id'] ?? null;
    }

}