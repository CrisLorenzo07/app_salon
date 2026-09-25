<?php

use Model\ActiveRecord;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/environment.php';

require 'functions.php';
require 'database.php';



ActiveRecord::setDB($db);
