<?php

require_once __DIR__ . '/../includes/app.php';

use Controllers\APIController;
use Controllers\AppointmentController;
use Controllers\LoginController;
use MVC\Router;

$router = new Router();

$router->get('/', [LoginController::class, 'login']);
$router->post('/', [LoginController::class, 'login']);
$router->get('/logout', [LoginController::class, 'logout']);
$router->get('/forgot-password', [LoginController::class, 'forgotPassword']);
$router->post('/forgot-password', [LoginController::class, 'forgotPassword']);
$router->get('/create-account', [LoginController::class, 'create']);
$router->post('/create-account', [LoginController::class, 'create']);
$router->get('/confirm-account', [LoginController::class, 'confirmAccount']);
$router->get('/message', [LoginController::class, 'message']);
$router->get('/reset-password', [LoginController::class, 'resetPassword']);
$router->post('/reset-password', [LoginController::class, 'resetPassword']);

$router->get('/appointment', [AppointmentController::class, 'index']);

$router->get('/api/services', [APIController::class, 'index']);




// Compatibility aliases for existing links.
$router->get('/api/servicios', $router->getRoutes['/api/services']);
$router->get('/crear-cuenta', $router->getRoutes['/create-account']);
$router->post('/crear-cuenta', $router->postRoutes['/create-account']);
$router->get('/confirmar-cuenta', $router->getRoutes['/confirm-account']);
$router->get('/recuperar', $router->getRoutes['/reset-password']);
$router->post('/recuperar', $router->postRoutes['/reset-password']);
$router->get('/olvide', $router->getRoutes['/forgot-password']);
$router->post('/olvide', $router->postRoutes['/forgot-password']);
$router->get('/mensaje', $router->getRoutes['/message']);
$router->get('/cita', $router->getRoutes['/appointment']);

$router->checkRoutes();
