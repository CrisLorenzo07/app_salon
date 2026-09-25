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
            session_start();
        }

        $currentUrl = strtok($_SERVER['REQUEST_URI'], '?') ?? '/';
        $method = $_SERVER['REQUEST_METHOD'];

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
