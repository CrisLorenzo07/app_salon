<?php

namespace Controllers;

use Model\Appointment;
use Model\Service;

class APIController
{
    public static function index()
    {
        $services = Service::all();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($services);
    }

    public static function save()
    {
        header('Content-Type: application/json; charset=utf-8');

        $appointment = new Appointment([
            'date' => $_POST['date'] ?? '',
            'time' => $_POST['time'] ?? '',
            'user_id' => (int) ($_POST['user_id'] ?? $_SESSION['id'] ?? 0),
        ]);

        try {
            $result = $appointment->save();
        } catch (\mysqli_sql_exception $error) {
            error_log('Error al guardar cita: ' . $error->getMessage());
            $result = ['result' => false, 'id' => 0];
        }

        echo json_encode($result);
    }
}
