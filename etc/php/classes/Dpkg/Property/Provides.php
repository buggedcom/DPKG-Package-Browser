<?php

namespace DpkgBrowser\Classes\Dpkg\Property;

use DpkgBrowser\Classes\Cache;
use DpkgBrowser\Classes\Dpkg\Package;
use DpkgBrowser\Classes\Dpkg\PackageVersionWithAlternatives;

/**
 * Class Provides
 * Holds the package "Provides" property.
 * The value is an array of \DpkgBrowser\Dpkg\PackageVersion instances.
 *
 * @author Oliver Lillie
 * @package DpkgBrowser\Classes\Dpkg\Property
 */
class Provides extends _HasPackageVersion implements _IntegratePackageInterface{

    /**
     * @inheritdoc
     *
     * This caches any provided packages to a map that leads back to the parent
     * package.
     *
     * @author Oliver Lillie
     */
    public function integratePackage(Package $package) {
        $pool = Cache::getPool();
        $item = $pool->getItem('dpkg/provided_to_package_map');
        $mapping = $item->get();
        if($item->isMiss() === true) {
            $mapping = [];
        }

        // this must be via getPropertyBag and not the magic property since
        // there is a chicken and egg situation as this function is called when
        // processing getPropertyBag before the property bag process has
        // completed
        $package_name = $package->getPropertyBag()->package->value;

        foreach ($this->_value as $package_version) {
            if($package_version instanceof PackageVersionWithAlternatives === true) {
                foreach ($package_version as $alt_package_version) {
                    $mapping[$alt_package_version->package_name] = $package_name;
                }
            } else {
                $mapping[$package_version->package_name] = $package_name;
            }
        }
        $pool->save(
            $item->set($mapping)
        );
    }
}