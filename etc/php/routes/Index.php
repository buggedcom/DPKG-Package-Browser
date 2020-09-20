<?php

namespace DpkgBrowser\Routes;

use DpkgBrowser\Classes\BaseRoute;
use DpkgBrowser\Classes\Escape;

/**
 * Class Index
 *
 * Sets up the index page.
 *
 * @author Oliver Lillie
 * @package DpkgBrowser\Routes
 */
class Index extends BaseRoute {
    public function get(): void {
        header('Content-type: text/html');
        echo file_get_contents(ETC . 'vue/dist/index.html');
        exit;
    }
}
