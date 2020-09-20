<?php

namespace DpkgBrowser\Classes\Dpkg;

use ArrayAccess;
use Countable;
use Iterator;
use DpkgBrowser\Classes\JsonPayloadDataInterface;
use DpkgBrowser\Traits\ArrayContainer;

/**
 * Class PackageVersionWithAlternatives
 *
 * A container for a list of PackageVersions that are alternatives to each
 * other.
 *
 * @author Oliver Lillie
 * @package DpkgBrowser\Classes\Dpkg
 */
class PackageVersionWithAlternatives implements ArrayAccess, Iterator, Countable, JsonPayloadDataInterface {

    use ArrayContainer;

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

        foreach ($this->_manifest as $package_version) {
            $return[] = $package_version->encodeForJson();
        }

        return $return;
    }

}