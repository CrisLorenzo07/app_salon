<?php

namespace Controllers;

use Classes\Email;
use Model\Installation;
use Model\RequestLimit;
use Model\User;
use MVC\Router;

class InstallationController
{
    private static function validCsrf(): bool
    {
        $token = $_POST['csrf_token'] ?? null;
        return is_string($token) && isset($_SESSION['installation_csrf'])
            && hash_equals($_SESSION['installation_csrf'], $token);
    }

    public static function create(Router $router): void
    {
        header('Cache-Control: no-store');
        header('Referrer-Policy: no-referrer');
        if (!Installation::available()) {
            http_response_code(404);
            $router->render('auth/installation', ['available' => false, 'alerts' => [], 'user' => new User()]);
            return;
        }
        $_SESSION['installation_csrf'] ??= bin2hex(random_bytes(32));
        if (isset($_GET['token'])) {
            $token = $_GET['token'];
            unset($_SESSION['installation_invitation']);
            if (is_string($token) && preg_match('/^[a-f0-9]{64}$/D', $token)) {
                $hash = hash('sha256', $token);
                if (Installation::validInvitation($hash)) {
                    session_regenerate_id(true);
                    $_SESSION['installation_invitation'] = $hash;
                    $_SESSION['installation_csrf'] = bin2hex(random_bytes(32));
                }
            }
            if (empty($_SESSION['installation_invitation'])) {
                $_SESSION['installation_link_error'] = true;
            }
            // Quitar el enlace privado de la URL antes de mostrar el formulario.
            header('Location: /configuracion-inicial', true, 303);
            return;
        }
        $invitation = $_SESSION['installation_invitation'] ?? '';
        $authorized = is_string($invitation) && Installation::validInvitation($invitation);
        $user = new User();
        $user->email = Installation::ownerEmail();
        $alerts = [];
        if (!empty($_SESSION['installation_link_error'])) {
            $alerts['error'][] = 'El enlace no es válido o ha caducado. Solicita uno nuevo.';
            unset($_SESSION['installation_link_error']);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach (['name', 'last_name', 'phone', 'password'] as $field) {
                $user->$field = is_string($_POST[$field] ?? null) ? $_POST[$field] : '';
            }
            try {
                if (!self::validCsrf()) {
                    http_response_code(403);
                    $alerts['error'][] = 'La sesión del formulario no es válida. Recarga la página.';
                } elseif (($_POST['action'] ?? '') === 'send-link') {
                    if (!RequestLimit::allowAttempt('initial-invitation')) {
                        http_response_code(429);
                        $alerts['error'][] = 'Demasiadas solicitudes. Espera 15 minutos.';
                    } else {
                        $token = Installation::issueInvitation();
                        $sent = (new Email('Administrador', Installation::ownerEmail(), $token))->sendInstallationInvitation();
                        $alerts[$sent ? 'success' : 'error'][] = $sent
                            ? 'Enviamos el enlace al correo del Administrador configurado para esta instalación.'
                            : 'No se pudo enviar el enlace. Intenta nuevamente más tarde.';
                    }
                } elseif (!RequestLimit::allowAttempt('initial-setup')) {
                    http_response_code(429);
                    $alerts['error'][] = 'Demasiados intentos. Espera 15 minutos antes de volver a intentar.';
                } elseif (!$authorized) {
                    $alerts['error'][] = 'Abre el enlace enviado al correo del Administrador antes de crear la cuenta.';
                } else {
                    $alerts = $user->validateNewAccount();
                    if (empty($alerts) && $user->userExists()->num_rows) {
                        $alerts = User::getAlerts();
                    }
                    if (empty($alerts)) {
                        Installation::createAdministrator($user, $invitation);
                        unset($_SESSION['installation_invitation']);
                        session_regenerate_id(true);
                        unset($_SESSION['installation_csrf']);
                        $_SESSION['installation_success'] = true;
                        header('Location: /iniciar-sesion', true, 303);
                        return;
                    }
                }
            } catch (\Throwable $error) {
                error_log('Error en alta inicial: ' . $error->getMessage());
                $alerts['error'][] = ($_POST['action'] ?? '') === 'send-link'
                    ? 'No se pudo enviar el enlace. Intenta nuevamente más tarde.'
                    : 'No se pudo completar la configuración. Si ya fue completada, inicia sesión.';
            }
        }
        $router->render('auth/installation', ['available' => true, 'authorized' => $authorized, 'alerts' => $alerts, 'user' => $user]);
    }
}
