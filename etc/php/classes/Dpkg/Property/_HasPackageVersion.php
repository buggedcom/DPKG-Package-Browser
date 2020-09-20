<?php

namespace DpkgBrowser\Classes\Dpkg\Property;

use DpkgBrowser\Classes\Dpkg\PackageVersion;
use DpkgBrowser\Classes\Dpkg\PackageVersionWithAlternatives;

/**
 * Class _HasPackageVersion
 *
 * Post processes list based properties so that each item in the list is an
 * instance of \DpkgBrowser\Classes\Dpkg\PackageVersion.
 *
 * @author Oliver Lillie
 * @package DpkgBrowser\Classes\Dpkg\Property
 * @property \DpkgBrowser\Classes\Dpkg\PackageVersion[] $value
 */
class _HasPackageVersion extends _List {

    /**
     * Post processes the item in the list and returns an instance of
     * \DpkgBrowser\Classes\Dpkg\PackageVersion or
     * \DpkgBrowser\Classes\Dpkg\PackageVersionWithAlternatives if the versions are
     * a piped separated list of alternatives.
     *
     * @author Oliver Lillie
     *
     * @param string $item The list item to post process.
     *
     * @return \DpkgBrowser\Classes\Dpkg\PackageVersion|\DpkgBrowser\Classes\Dpkg\PackageVersionWithAlternatives
     * @throws \InvalidArgumentException
     */
    protected function _postProcessListItem(string $item) {
        // check if the package is simple instance, or if it contains
        // alternatives which are pipe `|` separated
        if(preg_match('/(\s*\|\s*)/', $item, $matches) > 0) {
            $alternatives = array_map(
                function($dependancy_string) {
                    return new PackageVersion($dependancy_string);
                },
                preg_split('/(\s*\|\s*)/', $item)
            );
            return new PackageVersionWithAlternatives($alternatives);
        }

        return new PackageVersion($item);
    }

    /**
     * @inheritdoc
     * @author Oliver Lillie
     */
    public function encodeForJson() {
        return array_map(
            function ($item) {
                return $item->encodeForJson();
            },
            $this->_value
        );
    }
    
}