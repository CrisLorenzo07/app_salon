<?php

require 'functions.php';
require 'database.php';
require __DIR__ . '/../vendor/autoload.php';

use Model\ActiveRecord;
ActiveRecord::setDB($db);
