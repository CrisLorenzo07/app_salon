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
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            http_response_code(400);
            return;
        }
        $service = Service::find($id);
        if (!$service) {
            http_response_code(404);
            return;
        }
        $alert = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $alert = $service->validate($_POST);

            if (empty($alert)) {
                $service->save();
                header('Location: /servicios');
                return;
            }
        }

        $router->render('services/service-update', [
            'name' => $_SESSION['name'] ?? '',
            'service' => $service,
            'priceValue' => $_SERVER['REQUEST_METHOD'] === 'POST'
                ? (is_string($_POST['price'] ?? null) ? $_POST['price'] : '')
                : (string) $service->price,
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
            http_response_code(400);
            return;
        }

        $service = Service::find((int) $id);
        if (!$service) {
            http_response_code(404);
            return;
        }
        if ($service->delete()) {
            header('Location: /servicios');
            return;
        }
    }
}