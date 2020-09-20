<?php

namespace DpkgBrowser\Traits\Dpkg;

use DpkgBrowser\Classes\Args;
use DpkgBrowser\Classes\Dpkg\PropertyCollection;
use DpkgBrowser\Classes\Tick;

/**
 * Trait PropertyFilter
 *
 * Adds filter ability to the classes that uses the trait.
 *
 * @author Oliver Lillie
 * @package DpkgBrowser\Traits\Dpkg
 */
trait PropertyFilter {

    /**
     * Allows filterng of the current property maifest and then returns a new
     * PropertyCollection with the filtered properties.
     *
     * It does not update the current manifest.
     *
     * @author Oliver Lillie
     *
     * @param string $property
     * @param string $value
     * @param bool $strict
     *
     * @return PropertyCollection
     */
    public function filter(string $property, ?string $value, bool $strict = false): PropertyCollection {
        $filtered = [];

        foreach ($this->_manifest as $item) {
            if (
                ($strict === true && $item->{$property}->value === $value)
                || ($strict === false && strpos($item->{$property}->value, $value) !== false)
            ) {
                $filtered[] = $item;
            }
        }

        return new PropertyCollection($filtered);
    }

    /**
     * Filters the property bag of all the packages contained in the manifest to
     * only contain the provided properties.
     *
     * @author Oliver Lillie
     *
     * @param string ...$properties Must be a valid property value that is
     *  returned from PropertyBag::getValidPropertyList().
     *
     * @return \DpkgBrowser\Classes\Dpkg|\DpkgBrowser\Classes\Dpkg\PackageCollection
     */
    public function filterPropertyBags(string ...$properties) {
        // $properties not validated here as they are validated through the
        // $package->filterPropertyBags functions.

        foreach ($this->_manifest as $package) {
            call_user_func_array([$package, 'filterPropertyBag'], $properties);
        }

        return $this;
    }

}
