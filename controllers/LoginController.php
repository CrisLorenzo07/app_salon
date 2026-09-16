<?php

namespace Controllers;

use Classes\Email;
use Model\Usuario;
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
        $router->render('auth/olvide-password', [

        ]);
    }

    public static function recuperar(Router $router)
    {
        echo "Desde recuperar";
    }

    public static function crear(Router $router)
    {
        $alertas = [];
        $usuario = new Usuario($_POST);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevaCuenta();

            if (empty($alertas)) {
                $resultado = $usuario->existeUsuario();
                if ($resultado->num_rows) {
                    $alertas = Usuario::getAlertas();
                } else {
                    $usuario->hashPassword();
                    $usuario->crearToken();
                    $email = new Email($usuario->name, $usuario->email, $usuario->token);
                    $email->enviarConfirmacion();
                    $resultado = $usuario->guardar();
                    if ($resultado) {
                        header('Location: /mensaje');
                    }
                }
            }
        }

        $router->render('auth/crear-cuenta', [
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function mensaje(Router $router)
    {
        $router->render('auth/mensaje');
    }

    public static function confirmarCuenta(Router $router)
    {
        $alertas = [];
        $token = $_GET['token'] ?? '';
        $usuario = null;

        if (is_string($token) && trim($token) !== '') {
            $usuario = Usuario::where('token', $token);
        }

        if (empty($usuario)) {
            Usuario::setAlerta('error', 'Token no válido');
        } else {
            $usuario->confirmed = 1;
            $usuario->token = '';

            try {
                $guardado = $usuario->guardar();
            } catch (\mysqli_sql_exception $e) {
                error_log('Error al confirmar cuenta: ' . $e->getMessage());
                $guardado = false;
            }

            if ($guardado) {
                Usuario::setAlerta('exito', 'Cuenta verificada correctamente');
            } else {
                Usuario::setAlerta('error', 'No se pudo verificar la cuenta');
            }
        }

        $alertas = Usuario::getAlertas();
        $router->render('auth/confirmar-cuenta', [
            'alertas' => $alertas
        ]);
    }
}
