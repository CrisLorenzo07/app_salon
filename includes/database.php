<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = ($_ENV['DB_HOST']);
$username = ($_ENV['DB_USER']);
$password = ($_ENV['DB_PASS']);
$databaseName = ($_ENV['DB_NAME']);
$port = (int) $_ENV['DB_PORT'];

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
