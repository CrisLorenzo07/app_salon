<?php

namespace MVC;

class Router
{
    public array $getRoutes = [];
    public array $postRoutes = [];

    public function get($url, $fn)
    {
        $this->getRoutes[$url] = $fn;
    }

    public function post($url, $fn)
    {
        $this->postRoutes[$url] = $fn;
    }

    public function checkRoutes()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            session_set_cookie_params([
                'httponly' => true,
                'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                    || str_starts_with($_ENV['APP_URL'] ?? '', 'https://'),
                'samesite' => 'Lax',
                'path' => '/',
            ]);
            session_start();
        }

        $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
        if (!empty($_SESSION['login'])) {
            if (time() - ($_SESSION['last_activity'] ?? time()) > 1800) {
                $_SESSION = ['csrf_token' => bin2hex(random_bytes(32))];
                session_regenerate_id(true);
            } else {
                $_SESSION['last_activity'] = time();
            }
        }

        $currentUrl = strtok($_SERVER['REQUEST_URI'], '?') ?? '/';
        $method = $_SERVER['REQUEST_METHOD'];

        // El asistente usa su propio token; las demás escrituras se protegen aquí.
        if ($method === 'POST' && $currentUrl !== '/configuracion-inicial') {
            $token = $_POST['csrf_token'] ?? null;
            if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
                http_response_code(403);
                if (str_starts_with($currentUrl, '/api/')) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['result' => false, 'message' => 'Recarga la página e inténtalo nuevamente.']);
                } else {
                    echo 'La sesión del formulario no es válida. Recarga la página e inténtalo nuevamente.';
                }
                return;
            }
        }

        if ($method === 'GET') {
            $fn = $this->getRoutes[$currentUrl] ?? null;
        } elseif ($method === 'POST') {
            $fn = $this->postRoutes[$currentUrl] ?? null;
        } else {
            http_response_code(405);
            header('Allow: GET, POST');
            return;
        }

        if ($fn) {
            call_user_func($fn, $this);
        } else {
            http_response_code(404);
            echo "Página No Encontrada o Ruta no válida";
        }
    }

    public function render($view, $data = [])
    {
        foreach ($data as $key => $value) {
            $$key = $value;
        }

        ob_start();

        include_once __DIR__ . "/views/$view.php";
        $content = ob_get_clean();
        include_once __DIR__ . '/views/layout.php';
    }
}
