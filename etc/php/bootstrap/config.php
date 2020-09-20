<?php

namespace DpkgBrowser;

use Respect\Config\Container;

$config = new Container(ETC . 'backend.ini');

define('ENVIRONMENT', $config->environment);

if(ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
}