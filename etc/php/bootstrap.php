<?php

error_reporting(E_ALL);

define('ROOT', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);
define('ETC', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('PATH', __DIR__ . DIRECTORY_SEPARATOR);

chdir(PATH);

require_once './bootstrap/autoload.php';
require_once './bootstrap/config.php';
require_once './bootstrap/routes.php';
