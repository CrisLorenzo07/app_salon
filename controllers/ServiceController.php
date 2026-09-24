<?php

namespace Controllers;

use MVC\Router;
use Model\Service;

class ServiceController
{
    public static function index(Router $router)
    {

        isAdmin();
        $router->render('services/index', [
            'name' => $_SESSION['name'] ?? '',
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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        }

        $router->render('services/service-update', [
            'name' => $_SESSION['name'] ?? ''
        ]);
    }





    public static function delete()
    {
        isAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        }

    }
}