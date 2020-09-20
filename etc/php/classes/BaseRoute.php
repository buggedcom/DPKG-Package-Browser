<?php

namespace DpkgBrowser\Classes;

use Respect\Rest\Routable;
use Respect\Config\Container;

/**
 * Class BaseRoute
 *
 * The base route object for setting up routing of mehtods of the HTTP requests.
 *
 * @author Oliver Lillie
 * @package DpkgBrowser\Classes
 */
class BaseRoute implements Routable {

    public function __construct() {
        $this->_sendHeaders();
    }

    /**
     * Sends no-cache and access control related headers.
     *
     * @author Oliver Lillie
     * @return void
     */
    private function _sendHeaders(): void {
        Header::noCache();
        Header::accessControl();
    }

    /**
     * Returns an instance of the config container.
     *
     * @author Oliver Lillie
     * @return \Respect\Config\Container
     */
    public function config(): Container {
        static $config = null;
        if ($config !== null) {
            return $config;
        }

        $config = new Container(ETC . 'backend.ini');

        return $config;
    }

    /**
     * Returns an instance of the dpkg parser.
     *
     * @author Oliver Lillie
     * @return \DpkgBrowser\Classes\Dpkg|\DpkgBrowser\Classes\Dpkg\Package[]
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function dpkg(): Dpkg {
        static $dpkg = null;
        if ($dpkg !== null) {
            return $dpkg;
        }

        $dpkg = new Dpkg($this->config());

        return $dpkg;
    }

}