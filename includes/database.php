<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = '127.0.0.1';
$username = 'app_salon_user';
$password = 'app_salon_passwd';
$databaseName = 'app_salon_db';
$port = 3306;

try {
    $db = new mysqli(
        $host,
        $username,
        $password,
        $databaseName,
        $port
    );

    $db->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    error_log('Error de conexión MySQL: ' . $e->getMessage());

    http_response_code(500);
    exit('No se pudo conectar a la base de datos.');
}
