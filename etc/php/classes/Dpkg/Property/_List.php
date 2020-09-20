<?php

namespace DpkgBrowser\Classes\Dpkg\Property;

use DpkgBrowser\Classes\Args;
use DpkgBrowser\Classes\Dpkg\Property;

/**
 * Class _List
 *
 * Processes list based properties so that each item in the list is a string.
 *
 * @author Oliver Lillie
 * @package DpkgBrowser\Classes\Dpkg\Property
 * @property string[] $value
 */
class _List extends Property {

    /**
     * @type string A regex used to split the properties value.
     * @author Oliver Lillie
     */
    protected $_delimiter = '/(,\s*)/';

    /**
     * Post processes the item in the list and returns an instance of
     * \DpkgBrowser\Classes\Dpkg\PackageVersion.
     *
     * @author Oliver Lillie
     *
     * @param string $item The list item to post process.
     *
     * @return string
     */
    protected function _postProcessListItem(string $item) {
        return $item;
    }

    /**
     * If a value is provided then the value is split and each is processed
     * through _List._postProcessListItem to allow child classes to process the
     * value.
     *
     * @author Oliver Lillie
     *
     * @param string|null $value
     *
     * @return self
     */
    public function setValue(?string $value): Property {
        if ($value !== null) {
            $list = preg_split($this->_delimiter, $value);
            $this->_value = array_map([$this, '_postProcessListItem'], $list);
        } else {
            $this->_value = [];
        }

        return $this;
    }
    
}