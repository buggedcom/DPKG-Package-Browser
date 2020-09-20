<?php

namespace DpkgBrowser\Classes\Dpkg;

use ArrayAccess;
use Countable;
use Iterator;
use DpkgBrowser\Traits\ArrayContainer;

/**
 * A simplified instance of a PropertyBag that has been filtered.
 *
 * @author Oliver Lillie
 * @package DpkgBrowser\Classes\Dpkg
 */
class PropertyCollection implements ArrayAccess, Iterator, Countable {

    use ArrayContainer;

    /**
     * The data container from ArrayContainer.
     *
     * @type \DpkgBrowser\Classes\Dpkg\Property[]
     * @author Oliver Lillie
     */
    protected $_manifest;

}