<?php

namespace DpkgBrowser\Classes\Dpkg\Property;

use DpkgBrowser\Classes\Dpkg\Package;

/**
 * Interface _PostGetPackageProcessInterface
 *
 * If properties require some kind of post process to interact with the parent
 * package when created then them must extend this interface.
 *
 * @author Oliver Lillie
 * @package DpkgBrowser\Classes\Dpkg\Property
 * @property string[] $value
 */
interface _IntegratePackageInterface {

    /**
     * @author Oliver Lillie
     *
     * @param \DpkgBrowser\Classes\Dpkg\Package $package
     *
     * @return mixed
     */
    public function integratePackage(Package $package);

}