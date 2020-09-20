<?php

namespace DpkgBrowser\Classes\Dpkg;

use ArrayAccess;
use Countable;
use Iterator;
use DpkgBrowser\Classes\JsonPayloadDataInterface;
use DpkgBrowser\Traits\ArrayContainer;
use DpkgBrowser\Traits\Dpkg\PropertyFilter;

/**
 * class PackageCollection
 *
 * A container for a list of packages.
 *
 * @author Oliver Lillie
 * @package DpkgBrowser\Classes\Dpkg
 */
class PackageCollection implements ArrayAccess, Iterator, Countable, JsonPayloadDataInterface {

    use ArrayContainer;
    use PropertyFilter;

    /**
     * The data container from ArrayContainer.
     *
     * @type \DpkgBrowser\Classes\Dpkg\Package[]
     * @author Oliver Lillie
     */
    protected $_manifest;

    /**
     * @inheritdoc
     * @author Oliver Lillie
     * @return array
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function encodeForJson(): array {
        $return = [];

        foreach ($this->_manifest as $package) {
            $return[] = $package->encodeForJson();
        }

        return $return;
    }

}