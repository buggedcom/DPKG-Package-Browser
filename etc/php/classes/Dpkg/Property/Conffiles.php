<?php

namespace DpkgBrowser\Classes\Dpkg\Property;

use DpkgBrowser\Classes\Args;
use DpkgBrowser\Classes\Dpkg\Property;
use DpkgBrowser\Classes\Dpkg\Property\Conffiles\File;

/**
 * Class Conffiles
 *
 * Holds the package "Conffiles" property.
 *
 * @author Oliver Lillie
 * @package DpkgBrowser\Classes\Dpkg\Property
 * @property array[] $value
 */
class Conffiles extends _List {

    /**
     * @type string A regex used to split the properties value.
     * @author Oliver Lillie
     */
    protected $_delimiter = '/\n\s*/';

    /**
     * Post processes the item in the list and returns an instance of
     * \DpkgBrowser\Classes\Dpkg\Property\Conffiles\File.
     *
     * @author Oliver Lillie
     *
     * @param string $item The list item to post process.
     *
     * @return \DpkgBrowser\Classes\Dpkg\Property\Conffiles\File
     * @throws \InvalidArgumentException
     */
    protected function _postProcessListItem(string $item): File {
        $value = trim($item);
        [$path, $hash] = explode(' ', $value);

        return new File($path, $hash);
    }

}