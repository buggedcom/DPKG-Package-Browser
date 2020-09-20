<?php

namespace DpkgBrowser;

use Respect\Rest\Router;
use DpkgBrowser\Routes\Packages;
use DpkgBrowser\Routes\Index;
use DpkgBrowser\Routes\Package;

// sets up the incoming url request to map to the related rouutes.

$router = new Router;
$router->isAutoDispatched = false;

// The index page.
$router->get('/', Index::class);

// The package list.
$router->get('/packages', Packages::class);

// Specific information about a package.
$router->get('/packages/*', Package::class);

echo $router->run();
exit;