<?php

namespace DpkgBrowser\Classes;

use DpkgBrowser\Classes\Dpkg\Package;
use DpkgBrowser\Classes\Dpkg\PackageCollection;
use DpkgBrowser\Classes\Dpkg\PackageVersion;
use DpkgBrowser\Classes\Dpkg\PackageVersionWithAlternatives;
use DpkgBrowser\Traits\Dpkg\PropertyFilter;
use DpkgBrowser\Classes\Dpkg\Parser;
use Respect\Config\Container;

/**
 * Class Dpkg
 *
 * This is the base dpkg status parser.
 *
 * @author Oliver Lillie
 * @package DpkgBrowser\Classes
 */
class Dpkg extends Parser {

    use PropertyFilter;

    /**
     * Dpkg constructor.
     *
     * @access public
     * @author Oliver Lillie
     *
     * @param \Respect\Config\Container $config The configuration instance.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function __construct(Container $config) {
        parent::__construct($config->dpkp_status_path);
    }

    /**
     * Calculates the dependants (ie other packages that use the given package)
     * for the given package.
     *
     * @author Oliver Lillie
     *
     * @param \DpkgBrowser\Classes\Dpkg\Package $package
     *
     * @return \DpkgBrowser\Classes\Dpkg\PackageCollection
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \ReflectionException
     * @throws \Stash\Exception\RuntimeException
     */
    public function getDependantsForPackage(Package $package): PackageCollection {
        // get the packages cache item for the dependants to see if it has
        // already been calcuated because there is a huge performance gain
        // by storing the values caches since it requires parsing all the
        // properties of all the packages.
        $item = $package->getPackageCacheItem('dependants');
        $collection = $item->get();
        if($item->isHit() === true) {
            return $collection;
        }

        $package_name = $package->package->value;

        $dependants = [];
        foreach ($this->_manifest as $other_package) {
            foreach ($this->_getDependenciesForPackage($other_package) as $depency) {
                if ($depency->package_name === $package_name) {
                    $dependants[] = $other_package;
                    break;
                }
            }
        }

        $collection = new PackageCollection($dependants);

        Cache::getPool()->save(
            $item->set($collection)
        );

        return $collection;
    }

    /**
     * Returns a map of package names to list of packages they provide.
     *
     * @author Oliver Lillie
     * @return array
     * @throws \InvalidArgumentException
     * @throws \Stash\Exception\RuntimeException
     */
    public function getPackageToProvidedMap(): array {
        $pool = Cache::getPool();
        $item = $pool->getItem('dpkg/provided_to_package_map');
        $mapping = $item->get();
        if($item->isHit() === true) {
            return $mapping;
        }

        foreach ($this->_manifest as $package) {
            $provides = $package->provides->value;
            if(empty($provides) === true) {
                continue;
            }

            $package_name = $package->package->value;
            foreach ($provides as $package_version) {
                if($package_version instanceof PackageVersionWithAlternatives === true) {
                    foreach ($package_version as $alt_package_version) {
                        $mapping[$alt_package_version->package_name] = $package_name;
                    }
                } else {
                    $mapping[$package_version->package_name] = $package_name;
                }
            }
        }

        $pool->save(
            $item->set($mapping)
        );

        return $mapping;
    }

    /**
     * Returns a list of dependancy PackageVersion objects related to the
     * provided package.
     *
     * @author Oliver Lillie
     *
     * @param \DpkgBrowser\Classes\Dpkg\Package $package
     *
     * @return \DpkgBrowser\Classes\Dpkg\PackageVersion[]
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \ReflectionException
     */
    private function _getDependenciesForPackage(Package $package): array {
        $dependancies = [];

        if (is_array($package->depends->value) === true) {
            $dependancies = array_merge($dependancies, $this->_getFlattenedDependants($package->depends->value));
        }
        if (is_array($package->preDepends->value) === true) {
            $dependancies = array_merge($dependancies, $this->_getFlattenedDependants($package->preDepends->value));
        }

        return $dependancies;
    }

    /**
     * Flattens dependancy values since some may be instances of
     * PackageVersionWithAlternatives.
     *
     * @author Oliver Lillie
     *
     * @param \DpkgBrowser\Classes\Dpkg\PackageVersionWithAlternatives[]|\DpkgBrowser\Classes\Dpkg\PackageVersion[] $depends
     *
     * @return \DpkgBrowser\Classes\Dpkg\PackageVersion[]
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \ReflectionException
     */
    private function _getFlattenedDependants(array $depends): array {
        Args::v(
            $depends, Args::arrayType()->each(Args::oneOf(
                Args::instance(PackageVersionWithAlternatives::class),
                Args::instance(PackageVersion::class)
            ))
        );

        $flattened = [];
        foreach ($depends as $index => $depend) {
            if ($depend instanceof PackageVersionWithAlternatives === true) {
                $flattened = array_merge($flattened, $depend->toArray());
            } else {
                $flattened[] = $depend;
            }
        }

        return $flattened;
    }

}