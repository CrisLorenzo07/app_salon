<?php

namespace Controllers;

use Classes\Email;
use Model\RequestLimit;
use Model\User;
use MVC\Router;

class LoginController
{
    public static function login(Router $router)
    {
        $alerts = [];
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_SESSION['installation_success'])) {
            User::setAlert('success', 'Administrador creado correctamente. Ya puedes iniciar sesión.');
            unset($_SESSION['installation_success']);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!self::allowRequest('login')) {
                $router->render('auth/login', ['alerts' => User::getAlerts()]);
                return;
            }
            $auth = new User([
                'email' => is_string($_POST['email'] ?? null) ? $_POST['email'] : '',
                'password' => is_string($_POST['password'] ?? null) ? $_POST['password'] : '',
            ]);

            $alerts = $auth->validateLogin();

            if (empty($alerts)) {
                $user = User::where('email', $auth->email);

                if ($user) {
                    if ($user->verifyPasswordAndConfirmation($auth->password)) {
                        if (session_status() !== PHP_SESSION_ACTIVE) {
                            session_start();
                        }

                        session_regenerate_id(true);

                        $_SESSION['id'] = $user->id;
                        $_SESSION['name'] = $user->name . " " . $user->last_name;
                        $_SESSION['email'] = $user->email;
                        $_SESSION['login'] = true;
                        $_SESSION['last_activity'] = time();

                        $_SESSION['admin'] = $user->admin;

                        if ($user->admin === 1) {
                            header('Location: /admin');
                        } else {
                            header('Location: /cita');
                        }
                        return;
                    }
                } else {
                    // Igualar el coste del caso sin usuario para reducir la enumeración por tiempo.
                    password_verify($auth->password, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi');
                    User::setAlert('error', 'El email o la contraseña no son válidos, o la cuenta no está confirmada');
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
        $user = new User();
        if (!User::tokenStorageReady()) {
            http_response_code(503);
            $router->render('auth/create-account', ['user' => $user, 'alerts' => ['error' => ['El registro está temporalmente no disponible.']]]);
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!self::allowRequest('register')) {
                $router->render('auth/create-account', ['user' => $user, 'alerts' => User::getAlerts()]);
                return;
            }
            foreach (['name', 'last_name', 'phone', 'email', 'password'] as $field) {
                $user->$field = is_string($_POST[$field] ?? null) ? $_POST[$field] : '';
            }
            $alerts = $user->validateNewAccount();

            if (empty($alerts)) {
                $result = $user->userExists();
                if ($result->num_rows) {
                    $alerts = User::getAlerts();
                } else {
                    $user->hashPassword();
                    $token = $user->createToken('confirmation');
                    $result = $user->save();
                    if (!empty($result['result'])) {
                        try {
                            (new Email($user->name, $user->email, $token))->sendConfirmation();
                        } catch (\Throwable $error) {
                            error_log('No se pudo enviar confirmación de cliente: ' . $error->getMessage());
                            User::setAlert('error', 'La cuenta se guardó, pero no se pudo enviar el correo. Usa el formulario de recuperación para solicitar otra confirmación.');
                            $router->render('auth/create-account', ['user' => $user, 'alerts' => User::getAlerts()]);
                            return;
                        }
                        header('Location: /mensaje');
                        return;
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
        header('Cache-Control: no-store');
        header('Referrer-Policy: no-referrer');
        $token = $_GET['token'] ?? '';
        $user = User::forToken($token, 'confirmation');

        if (empty($user)) {
            User::setAlert('error', 'Token no válido');
        } else {
            try {
                $saved = User::consumeToken($token, 'confirmation');
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
        if (!User::tokenStorageReady()) {
            http_response_code(503);
            $router->render('auth/forgot-password', ['alerts' => ['error' => ['La recuperación de cuentas está temporalmente no disponible.']]]);
            return;
        }
        $alerts = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!self::allowRequest('recovery')) {
                $router->render('auth/forgot-password', ['alerts' => User::getAlerts()]);
                return;
            }
            $auth = new User(['email' => is_string($_POST['email'] ?? null) ? $_POST['email'] : '']);
            $alerts = $auth->validateEmail();

            if (empty($alerts)) {
                $user = User::where('email', $auth->email);

                if ($user) {
                    $purpose = $user->confirmed === 1 ? 'reset' : 'confirmation';
                    $token = $user->createToken($purpose);
                    $user->save();
                    try {
                        $email = new Email($user->name, $user->email, $token);
                        $purpose === 'reset' ? $email->sendInstructions() : $email->sendConfirmation();
                    } catch (\Throwable $error) {
                        error_log('No se pudo enviar recuperación de cuenta: ' . $error->getMessage());
                    }
                }
                User::setAlert('success', 'Si la cuenta existe, recibirás instrucciones en tu email');
            }
        }
        $alerts = User::getAlerts();

        $router->render('auth/forgot-password', [
            'alerts' => $alerts
        ]);
    }

    public static function resetPassword(Router $router)
    {
        header('Cache-Control: no-store');
        header('Referrer-Policy: no-referrer');
        $alerts = [];
        $error = false;
        $token = $_GET['token'] ?? '';
        $user = User::forToken($token, 'reset');

        if (!$user) {
            User::setAlert('error', 'Token no válido');
            $error = true;
        }

        if (!$error && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $user->password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

            $alerts = $user->validatePassword(
                is_string($_POST['password_confirmation'] ?? null) ? $_POST['password_confirmation'] : ''
            );

            if (empty($alerts)) {
                $user->hashPassword();
                $result = User::consumeToken($token, 'reset', $user->password);

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
        $_SESSION = [];
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 3600,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
        session_destroy();
        header('Location: /');
        exit;
    }

    private static function allowRequest(string $action): bool
    {
        $scope = $action . ':' . substr(hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown'), 0, 48);
        try {
            if (RequestLimit::allowAttempt($scope)) {
                return true;
            }
            http_response_code(429);
            User::setAlert('error', 'Demasiados intentos. Espera 15 minutos antes de volver a intentar.');
        } catch (\Throwable $error) {
            error_log('No se pudo comprobar el límite de autenticación: ' . $error->getMessage());
            http_response_code(503);
            User::setAlert('error', 'No se pudo procesar la solicitud. Intenta nuevamente más tarde.');
        }
        return false;
    }
}
