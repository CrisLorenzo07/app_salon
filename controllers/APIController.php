<?php

namespace Controllers;

use Model\Service;

class APIController
{
    public static function index()
    {
        $services = Service::all();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($services);
    }
}
