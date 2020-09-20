<?php

namespace DpkgBrowser;

require_once PATH . '/vendor/autoload.php';

spl_autoload_register(
    function($class) {
        if(strpos($class, 'DpkgBrowser') !== false) {
            $parts = explode('\\', $class);
            switch($parts[1]) {
                case 'Routes' :
                case 'Classes' :
                case 'Traits' :
                    require_once PATH . strtolower($parts[1]) . '/' . implode('/', array_slice($parts, 2)) . '.php';
                    return;
            }
        }
    }
);