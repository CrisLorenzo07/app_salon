<?php

namespace Controllers;

use Model\Appointment;
use Model\Service;
use Model\ServiceAppointment;

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

        if (empty($_SESSION['login']) || empty($_SESSION['id'])) {
            http_response_code(401);
            echo json_encode(['result' => false, 'id' => 0]);
            return;
        }

        $appointment = new Appointment([
            'date' => $_POST['date'] ?? '',
            'time' => $_POST['time'] ?? '',
            'user_id' => (int) $_SESSION['id'],
        ]);

        $transactionStarted = false;
        try {
            $services = $_POST['services'] ?? '';
            if (!is_string($services) || trim($services) === '') {
                throw new \InvalidArgumentException('Debes seleccionar servicios.');
            }

            $serviceIds = [];
            foreach (explode(',', $services) as $serviceId) {
                $serviceId = filter_var(trim($serviceId), FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 1],
                ]);
                if ($serviceId === false) {
                    throw new \InvalidArgumentException('Servicio no válido.');
                }
                $serviceIds[] = $serviceId;
            }

            Appointment::beginTransaction();
            $transactionStarted = true;
            $result = $appointment->save();
            if (empty($result['result'])) {
                throw new \RuntimeException('No se pudo guardar la cita.');
            }

            $appointmentId = (int) $result['id'];
            foreach (array_unique($serviceIds) as $serviceId) {
                $serviceAppointment = new ServiceAppointment([
                    'service_id' => $serviceId,
                    'appointment_id' => $appointmentId,
                ]);

                $serviceResult = $serviceAppointment->save();
                if (empty($serviceResult['result'])) {
                    throw new \RuntimeException('No se pudo guardar un servicio.');
                }
            }

            Appointment::commit();
            $transactionStarted = false;
        } catch (\Throwable $error) {
            if ($transactionStarted) {
                try {
                    Appointment::rollback();
                } catch (\Throwable $rollbackError) {
                    error_log('Error al revertir cita: ' . $rollbackError->getMessage());
                }
            }
            error_log('Error al guardar cita: ' . $error->getMessage());
            $result = ['result' => false, 'id' => 0];
        }

        echo json_encode($result);
    }

    public static function delete()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (empty($_SESSION['login']) || ($_SESSION['admin'] ?? 0) !== 1) {
            http_response_code(403);
            echo json_encode(['result' => false, 'message' => 'No tienes permiso para eliminar citas.']);
            return;
        }
        $token = $_POST['csrf_token'] ?? '';
        if (!is_string($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            echo json_encode(['result' => false, 'message' => 'Recarga la página e inténtalo nuevamente.']);
            return;
        }
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            http_response_code(400);
            echo json_encode(['result' => false, 'message' => 'La cita no es válida.']);
            return;
        }
        try {
            $deleted = Appointment::deleteWithServices($id);
            http_response_code($deleted ? 200 : 404);
            echo json_encode(['result' => $deleted, 'message' => $deleted ? 'Cita eliminada.' : 'La cita ya no existe.']);
        } catch (\Throwable $error) {
            error_log('Error al eliminar cita: ' . $error->getMessage());
            http_response_code(500);
            echo json_encode(['result' => false, 'message' => 'No se pudo eliminar la cita. Inténtalo nuevamente.']);
        }
    }
}
