<?php

namespace Controllers;

use MVC\Router;

class LoginController
{
    public static function login(Router $router)
    {
        

        $router->render('auth/login');
    }

    public static function logout(Router $router)
    {
        echo "Desde logout";
    }

    public static function olvide(Router $router)
    {
        echo "Desde olvide";
    }

    public static function recuperar(Router $router)
    {
        echo "Desde recuperar";
    }

    public static function crear(Router $router)
    {
        echo "Desde crear";
    }
}