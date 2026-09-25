<?php

namespace Controllers;

use MVC\Router;


class AppointmentController
{
    public static function index(Router $router)
    {
        isAuth();

        $router->render('appointment/index', [
            'name' => $_SESSION['name'] ?? '',
            'id' => $_SESSION['id'] ?? '',
            'alerts' => []
        ]);
    }
}
