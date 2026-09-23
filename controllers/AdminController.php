<?php

namespace Controllers;

use MVC\Router;
use Model\AdminAppointment;

class AdminController
{

    public static function index(Router $router)
    {
        isAdmin();

        $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
        $date = $_GET['date'] ?? date('Y-m-d');
        $format = is_string($date) && preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{2}$/D', $date)
            ? 'd/m/y'
            : 'Y-m-d';
        $parsedDate = is_string($date) && !str_contains($date, "\0")
            ? \DateTimeImmutable::createFromFormat('!' . $format, $date)
            : false;
        if (!$parsedDate || $parsedDate->format($format) !== $date) {
            http_response_code(400);
            $router->render('admin/index', [
                'name' => $_SESSION['name'] ?? '',
                'date' => '',
                'appointments' => [],
                'alerts' => ['error' => ['Selecciona una fecha válida.']]
            ]);
            return;
        }
        $date = $parsedDate->format('Y-m-d');

        $query = "SELECT appointments.id, appointments.date, appointments.time, COALESCE(CONCAT(users.name, ' ', users.last_name), '') AS client, ";
        $query .= " COALESCE(users.email, '') AS email, COALESCE(users.phone, '') AS phone, COALESCE(services.name, '') AS service, COALESCE(services.price, 0) AS price ";
        $query .= " FROM appointments ";
        $query .= " LEFT OUTER JOIN users ";
        $query .= " ON appointments.user_id = users.id ";
        $query .= " LEFT OUTER JOIN service_appointments ";
        $query .= " ON service_appointments.appointment_id = appointments.id ";
        $query .= " LEFT OUTER JOIN services ";
        $query .= " ON services.id = service_appointments.service_id ";
        $query .= " WHERE appointments.date = '{$date}' ";
        $query .= " ORDER BY appointments.time, appointments.id, services.name ";

        $appointments = AdminAppointment::querySQL($query);

        $router->render('admin/index', [
            'name' => $_SESSION['name'] ?? '',
            'id' => $_SESSION['id'] ?? '',
            'date' => $date,
            'appointments' => $appointments,
            'alerts' => []
        ]);
    }
}
