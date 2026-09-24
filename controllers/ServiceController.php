<?php

namespace Controllers;

use MVC\Router;
use Model\Service;

class ServiceController
{
    public static function index(Router $router)
    {

        isAdmin();
        $services = Service::all();
        $router->render('services/index', [
            'name' => $_SESSION['name'] ?? '',
            'services' => $services,
            'alerts' => []
        ]);
    }

    public static function create(Router $router)
    {
        isAdmin();
        $service = new Service;
        $alert = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $alert = $service->validate($_POST);

            if (empty($alert)) {
                $result = $service->save();

                if (!empty($result['result'])) {
                    header('Location: /servicios');
                    return;
                }
            }
        }

        $router->render('services/service-create', [
            'name' => $_SESSION['name'] ?? '',
            'service' => $service,
            'priceValue' => is_string($_POST['price'] ?? null) ? $_POST['price'] : '',
            'alerts' => $alert
        ]);
    }

    public static function update(Router $router)
    {
        isAdmin();
        if (!is_numeric($_GET['id']))
            return;
        $service = Service::find($_GET['id']);
        $alert = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $service->sync($_POST);
            $alert = $service->validate();

            if (empty($alert)) {
                $service->save();
                header('Location: /servicios');
                return;
            }
        }

        $router->render('services/service-update', [
            'name' => $_SESSION['name'] ?? '',
            'service' => $service,
            'alerts' => $alert
        ]);
    }

    public static function delete()
    {
        isAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $id = $_POST['id'] ?? '';
        if (!is_string($id) || !ctype_digit($id) || (int) $id < 1) {
            return;
        }

        $service = Service::find((int) $id);
        if ($service && $service->delete()) {
            header('Location: /servicios');
            return;
        }
    }
}