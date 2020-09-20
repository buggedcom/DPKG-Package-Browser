<?php

namespace DpkgBrowser\Classes\Dpkg\Property;

use DpkgBrowser\Classes\Dpkg\Property;

/**
 * Class Package
 *
 * Holds the package "Package" property.
 *
 * @author Oliver Lillie
 * @package DpkgBrowser\Classes\Dpkg\Property
 */
class Package extends Property {


    /**
     * The setter for setting the "value" value.
     *
     * @author Oliver Lillie
     *
     * @param string|null $value Must be null or a string value.
     *
     * @return self
     */
    public function setValue(?string $value): Property {
        $this->_value = $value;

        return $this;
    }
}