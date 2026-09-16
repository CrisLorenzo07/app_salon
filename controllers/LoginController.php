<?php

namespace Controllers;

use Classes\Email;
use Model\Usuario;
use MVC\Router;

class LoginController
{
    public static function login(Router $router)
    {
        $alertas = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth = new Usuario($_POST);

            $alertas = $auth->validarLogin();

            if (empty($alertas)) {
                $usuario = Usuario::where('email', $auth->email);

                if ($usuario) {
                    if ($usuario->comprobarPasswordAndVerificado($auth->password)) {
                        session_start();

                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['name'] = $usuario->name . " " . $usuario->last_name;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['login'] = true;

                        if ($usuario->admin === "1") {
                            $_SESSION['admin'] = $usuario->admin ?? null;
                            header('Location: /admin');
                        } else {
                            header('Location: /cita');
                        }
                    }
                } else {
                    Usuario::setAlerta('error', 'Usuario no encontrado');
                }
            }
        }

        $alertas = Usuario::getAlertas();
        $router->render('auth/login', [
            'alertas' => $alertas
        ]);
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

    public static function olvide(Router $router)
    {

        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth = new Usuario($_POST);
            $alertas = $auth->validarEmail();

            if (empty($alertas)) {
                $usuario = Usuario::where('email', $auth->email);

                if ($usuario && $usuario->confirmed === 1) {
                    $usuario->crearToken();
                    $usuario->guardar();

                    $email = new Email($usuario->name, $usuario->email, $usuario->token);
                    $email->enviarInstrucciones();
                    Usuario::setAlerta('exito', 'Revisa tu email');
                } else {
                    Usuario::setAlerta('error', 'El Usuario no existe, o no esta confirmado');
                }
            }
        }
        $alertas = Usuario::getAlertas();

        $router->render('auth/olvide-password', [
            'alertas' => $alertas
        ]);
    }

    public static function recuperar(Router $router)
    {
        $alertas = [];
        $error = false;
        $usuario = null;

        $token = $_GET['token'] ?? '';

        if (is_string($token) && trim($token) !== '') {
            $usuario = Usuario::where('token', $token);
        }

        if (!$usuario) {
            Usuario::setAlerta('error', 'Token no válido');
            $error = true;
        }

        if (!$error && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->password = $_POST['password'] ?? '';

            $alertas = $usuario->validarPassword(
                $_POST['password_confirmation'] ?? ''
            );

            if (empty($alertas)) {
                $usuario->hashPassword();
                $usuario->token = '';
                $resultado = $usuario->guardar();

                if ($resultado) {
                    Usuario::setAlerta(
                        'exito',
                        'Contraseña actualizada correctamente'
                    );
                    header('Refresh: 3; url=/');
                } else {
                    Usuario::setAlerta(
                        'error',
                        'No se pudo actualizar la contraseña'
                    );
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/recuperar-password', [
            'alertas' => $alertas,
            'error' => $error
        ]);
    }

    public static function logout(Router $router)
    {
        echo "Desde logout";
    }
}


