<?php

require_once __DIR__ . '/../includes/app.php';

use Controllers\APIController;
use Controllers\AppointmentController;
use Controllers\LoginController;
use Controllers\AdminController;
use Controllers\ServiceController;
use MVC\Router;

$router = new Router();

$router->get('/', [LoginController::class, 'login']);
$router->post('/', [LoginController::class, 'login']);
$router->get('/cerrar-sesion', [LoginController::class, 'logout']);
$router->get('/olvide', [LoginController::class, 'forgotPassword']);
$router->post('/olvide', [LoginController::class, 'forgotPassword']);
$router->get('/crear-cuenta', [LoginController::class, 'create']);
$router->post('/crear-cuenta', [LoginController::class, 'create']);
$router->get('/confirmar-cuenta', [LoginController::class, 'confirmAccount']);
$router->get('/mensaje', [LoginController::class, 'message']);
$router->get('/recuperar', [LoginController::class, 'resetPassword']);
$router->post('/recuperar', [LoginController::class, 'resetPassword']);

$router->get('/cita', [AppointmentController::class, 'index']);

$router->get('/api/servicios', [APIController::class, 'index']);
$router->post('/api/citas', [APIController::class, 'save']);
$router->post('/api/eliminar', [APIController::class, 'delete']);

$router->get('/admin', [AdminController::class, 'index']);

$router->get('/servicios', [ServiceController::class, 'index']);
$router->get('/servicios/crear-servicio', [ServiceController::class, 'create']);
$router->post('/servicios/crear-servicio', [ServiceController::class, 'create']);
$router->get('/servicios/actualizar-servicio', [ServiceController::class, 'update']);
$router->post('/servicios/actualizar-servicio', [ServiceController::class, 'update']);
$router->post('/servicios/eliminar-servicio', [ServiceController::class, 'delete']);




$router->checkRoutes();
