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

        $appointment = new Appointment([
            'date' => $_POST['date'] ?? '',
            'time' => $_POST['time'] ?? '',
            'user_id' => (int) ($_POST['user_id'] ?? $_SESSION['id'] ?? 0),
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
}
