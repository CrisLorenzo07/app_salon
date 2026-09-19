<?php

namespace Controllers;

use Classes\Email;
use Model\User;
use MVC\Router;

class LoginController
{
    public static function login(Router $router)
    {
        $alerts = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth = new User($_POST);

            $alerts = $auth->validateLogin();

            if (empty($alerts)) {
                $user = User::where('email', $auth->email);

                if ($user) {
                    if ($user->verifyPasswordAndConfirmation($auth->password)) {
                        session_start();

                        $_SESSION['id'] = $user->id;
                        $_SESSION['name'] = $user->name . " " . $user->last_name;
                        $_SESSION['email'] = $user->email;
                        $_SESSION['login'] = true;

                        if ($user->admin === "1") {
                            $_SESSION['admin'] = $user->admin ?? null;
                            header('Location: /admin');
                        } else {
                            header('Location: /appointment');
                        }
                    }
                } else {
                    User::setAlert('error', 'Usuario no encontrado');
                }
            }
        }

        $alerts = User::getAlerts();
        $router->render('auth/login', [
            'alerts' => $alerts
        ]);
    }

    public static function create(Router $router)
    {
        $alerts = [];
        $user = new User($_POST);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user->sync($_POST);
            $alerts = $user->validateNewAccount();

            if (empty($alerts)) {
                $result = $user->userExists();
                if ($result->num_rows) {
                    $alerts = User::getAlerts();
                } else {
                    $user->hashPassword();
                    $user->createToken();
                    $email = new Email($user->name, $user->email, $user->token);
                    $email->sendConfirmation();
                    $result = $user->save();
                    if ($result) {
                        header('Location: /message');
                    }
                }
            }
        }

        $router->render('auth/create-account', [
            'user' => $user,
            'alerts' => $alerts
        ]);
    }

    public static function message(Router $router)
    {
        $router->render('auth/message');
    }

    public static function confirmAccount(Router $router)
    {
        $alerts = [];
        $token = $_GET['token'] ?? '';
        $user = null;

        if (is_string($token) && trim($token) !== '') {
            $user = User::where('token', $token);
        }

        if (empty($user)) {
            User::setAlert('error', 'Token no válido');
        } else {
            $user->confirmed = 1;
            $user->token = '';

            try {
                $saved = $user->save();
            } catch (\mysqli_sql_exception $e) {
                error_log('Error al confirmar cuenta: ' . $e->getMessage());
                $saved = false;
            }

            if ($saved) {
                User::setAlert('success', 'Cuenta verificada correctamente');
            } else {
                User::setAlert('error', 'No se pudo verificar la cuenta');
            }
        }

        $alerts = User::getAlerts();
        $router->render('auth/confirm-account', [
            'alerts' => $alerts
        ]);
    }

    public static function forgotPassword(Router $router)
    {

        $alerts = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth = new User($_POST);
            $alerts = $auth->validateEmail();

            if (empty($alerts)) {
                $user = User::where('email', $auth->email);

                if ($user && $user->confirmed === 1) {
                    $user->createToken();
                    $user->save();

                    $email = new Email($user->name, $user->email, $user->token);
                    $email->sendInstructions();
                    User::setAlert('success', 'Revisa tu email');
                } else {
                    User::setAlert('error', 'El Usuario no existe, o no esta confirmado');
                }
            }
        }
        $alerts = User::getAlerts();

        $router->render('auth/forgot-password', [
            'alerts' => $alerts
        ]);
    }

    public static function resetPassword(Router $router)
    {
        $alerts = [];
        $error = false;
        $user = null;

        $token = $_GET['token'] ?? '';

        if (is_string($token) && trim($token) !== '') {
            $user = User::where('token', $token);
        }

        if (!$user) {
            User::setAlert('error', 'Token no válido');
            $error = true;
        }

        if (!$error && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $user->password = $_POST['password'] ?? '';

            $alerts = $user->validatePassword(
                $_POST['password_confirmation'] ?? ''
            );

            if (empty($alerts)) {
                $user->hashPassword();
                $user->token = '';
                $result = $user->save();

                if ($result) {
                    User::setAlert(
                        'success',
                        'Contraseña actualizada correctamente'
                    );
                    header('Refresh: 3; url=/');
                } else {
                    User::setAlert(
                        'error',
                        'No se pudo actualizar la contraseña'
                    );
                }
            }
        }

        $alerts = User::getAlerts();

        $router->render('auth/reset-password', [
            'alerts' => $alerts,
            'error' => $error
        ]);
    }

    public static function logout(Router $router)
    {
        echo "Desde logout";
    }
}


