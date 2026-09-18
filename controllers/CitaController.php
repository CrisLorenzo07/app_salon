<?php

namespace Controllers;

use MVC\Router;


class CitaController
{
    public static function index(Router $router)
    {

        session_start($_POST);
        $router->render('cita/index', [
            'name' => $_SESSION['name']
        ]);
    }
}
