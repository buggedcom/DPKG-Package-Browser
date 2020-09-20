<?php

namespace DpkgBrowser\Routes;

use DpkgBrowser\Classes\BaseRoute;
use DpkgBrowser\Classes\JsonPayload;

/**
 * Class Packages.
 *
 * Lists out all the package names alphabetically.
 *
 * @author Oliver Lillie
 * @package DpkgBrowser\Routes
 */
class Packages extends BaseRoute {
    public function get(): void {
        $data = [];

        foreach($this->dpkg() as $package) {
            $data[(string)$package->package] = $package->description->summary;
        }

        ksort($data);

        (new JsonPayload($data))->output();
    }
}
