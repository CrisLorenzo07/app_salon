<?php

use Model\ActiveRecord;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/environment.php';

ini_set('display_errors', ($_ENV['APP_ENV'] ?? 'production') === 'development' ? '1' : '0');
ini_set('log_errors', '1');

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

require 'functions.php';
require 'database.php';
ActiveRecord::setDB($db);
