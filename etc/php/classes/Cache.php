<?php

namespace DpkgBrowser\Classes;

use Stash\Driver\Ephemeral;
use Stash\Pool;

class Cache {

    /**
     * Returns an instance of a configured filesystem cache driver.
     *
     * @author Oliver Lillie
     * @return \Stash\Pool
     * @throws \Stash\Exception\RuntimeException
     */
    public static function getPool(): Pool {
        return new Pool(
            new Ephemeral()
        );
    }

}