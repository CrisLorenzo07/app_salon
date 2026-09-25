<?php

// Dokploy inyecta variables del proceso; el archivo local sigue siendo opcional.
$environmentKeys = [
    'DB_HOST', 'DB_PORT', 'DB_USER', 'DB_PASS', 'DB_NAME', 'APP_URL',
    'EMAIL_HOST', 'EMAIL_PORT', 'EMAIL_USER', 'EMAIL_PASS',
    'EMAIL_AUTH', 'EMAIL_ENCRYPTION', 'EMAIL_FROM', 'EMAIL_FROM_NAME',
];

foreach ($environmentKeys as $key) {
    $value = getenv($key);
    if ($value !== false) {
        $_ENV[$key] = $value;
    }
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
unset($environmentKeys, $key, $value);
