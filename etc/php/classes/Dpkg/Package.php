<?php

namespace DpkgBrowser\Classes\Dpkg;

use DpkgBrowser\Classes\AccessibleProperties;
use DpkgBrowser\Classes\Args;
use DpkgBrowser\Classes\Cache;
use DpkgBrowser\Classes\Dpkg\Property\_IntegratePackageInterface;
use DpkgBrowser\Classes\JsonPayloadDataInterface;
use DpkgBrowser\Classes\Tick;

/**
 * Class Package
 *
 * Contains the information about a packed that exists in the dpkg status page.
 * Until a magic property is requested the PropertBag for the object is not
 * parsed.
 *
 * @author Oliver Lillie
 * @package DpkgBrowser\Classes\Dpkg
 *
 * @property string package_string
 * @property \DpkgBrowser\Classes\Dpkg\Property\Package $package
 * @property \DpkgBrowser\Classes\Dpkg\Property\Status $status
 * @property \DpkgBrowser\Classes\Dpkg\Property\Priority $priority
 * @property \DpkgBrowser\Classes\Dpkg\Property\Section $section
 * @property \DpkgBrowser\Classes\Dpkg\Property\InstalledSize $installedSize
 * @property \DpkgBrowser\Classes\Dpkg\Property\Maintainer $maintainer
 * @property \DpkgBrowser\Classes\Dpkg\Property\Architecture $architecture
 * @property \DpkgBrowser\Classes\Dpkg\Property\MultiArch $multiArch
 * @property \DpkgBrowser\Classes\Dpkg\Property\Source $source
 * @property \DpkgBrowser\Classes\Dpkg\Property\Version $version
 * @property \DpkgBrowser\Classes\Dpkg\Property\Depends $depends
 * @property \DpkgBrowser\Classes\Dpkg\Property\Description $description
 * @property \DpkgBrowser\Classes\Dpkg\Property\Homepage $homepage
 * @property \DpkgBrowser\Classes\Dpkg\Property\OriginalMaintainer $originalMaintainer
 * @property \DpkgBrowser\Classes\Dpkg\Property\Replaces $replaces
 * @property \DpkgBrowser\Classes\Dpkg\Property\Breaks $breaks
 * @property \DpkgBrowser\Classes\Dpkg\Property\Enhances $enhances
 * @property \DpkgBrowser\Classes\Dpkg\Property\Provides $provides
 * @property \DpkgBrowser\Classes\Dpkg\Property\Conflicts $conflicts
 * @property \DpkgBrowser\Classes\Dpkg\Property\Recommends $recommends
 * @property \DpkgBrowser\Classes\Dpkg\Property\Conffiles $conffiles
 * @property \DpkgBrowser\Classes\Dpkg\Property\Suggests $suggests
 * @property \DpkgBrowser\Classes\Dpkg\Property\PreDepends $preDepends
 * @property \DpkgBrowser\Classes\Dpkg\Property\Essential $essential
 * @property \DpkgBrowser\Classes\Dpkg\Property\BuiltUsing $builtUsing
 * @property \DpkgBrowser\Classes\Dpkg\Property\Origin $origin
 * @property \DpkgBrowser\Classes\Dpkg\Property\Bugs $bugs
 */
class Package extends AccessibleProperties implements JsonPayloadDataInterface {

    /**
     * The private property for the public accessor of "package_string".
     *
     * @type string
     * @author Oliver Lillie
     */
    private $_package_string;

    /**
     * Contains the cached instance of the PropertyBag containing all instances
     * of the Property's in the package.
     *
     * @type \DpkgBrowser\Classes\Dpkg\PropertyBag
     * @author Oliver Lillie
     */
    private $_property_bag;

    /**
     * Package constructor.
     *
     * @access public
     * @author Oliver Lillie
     *
     * @param string $package_string Must be a non empty string.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $package_string) {
        $this->_setAccessibleProperties([
            'package_string'
        ]);

        $this->package_string = $package_string;
    }

    /**
     * The setter for setting the "package_string" value.
     *
     * @author Oliver Lillie
     *
     * @param string $package_string Must be a non empty string.
     *
     * @return $this
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \ReflectionException
     */
    public function setPackageString(string $package_string): self {
        Args::v(
            $package_string, Args::notEmpty()
        );

        $this->_package_string = $package_string;

        return $this;
    }

    /**
     * Checks for a md5 hash match from the cached version of the previous
     * property parse. If the cache is valid then it is used as the property bag
     * instance and is returned. Otherwise null is returned.
     *
     * @author Oliver Lillie
     * @return \DpkgBrowser\Classes\Dpkg\PropertyBag
     * @throws \InvalidArgumentException
     * @throws \Stash\Exception\RuntimeException
     * @throws \LogicException
     */
    private function _getPropertyBagCache(): ?PropertyBag {
        // check the md5 file hash of a cached data set to see if the dpkg
        // status has changed. If it has not changed and we have a serialized
        // version of the manifest then we will use the cached value instead of
        // reparsing the file again for performance.
        $item = $this->getPackageCacheItem();
        $bag = $item->get();
        if ($item->isMiss() === false) {
            return $bag;
        }

        return null;
    }

    /**
     * Returns a cache item for the given path related to the package.
     *
     * @author Oliver Lillie
     *
     * @param string $path
     *
     * @return \Stash\Interfaces\ItemInterface
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Stash\Exception\RuntimeException
     */
    public function getPackageCacheItem(string $path = ''): \Stash\Interfaces\ItemInterface {
        $pool = Cache::getPool();

        return $pool->getItem('dpkg/package/' . md5($this->_package_string) . (empty($path) === false ? '/' . $path : ''));
    }

    /**
     * Caches the property bag instance for the current package.
     *
     * @author Oliver Lillie
     *
     * @param PropertyBag $bag
     *
     * @return void
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Stash\Exception\RuntimeException
     */
    private function _cachePropertyBag(PropertyBag $bag): void {
        Cache::getPool()->save(
            $this->getPackageCacheItem()->set($bag)
        );
    }

    /**
     * Returns the property objects parsed from the package string value.
     *
     * @author Oliver Lillie
     * @return Property[]
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    private function _parseProperties(): array {
        $properties = [];

        $lines = preg_split('/\n(?! )/', $this->_package_string);
        foreach ($lines as $line) {
            $properties[] = Property::get($line);
        }
        return $properties;
    }

    /**
     * Parses the "package_string" into individual properties and then returns
     * them in a cached PropertyBag instance.
     *
     * @author Oliver Lillie
     * @return \DpkgBrowser\Classes\Dpkg\PropertyBag
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function getPropertyBag(): PropertyBag {
        if ($this->_property_bag !== null) {
            return $this->_property_bag;
        }

        $this->_property_bag = $this->_getPropertyBagCache();
        if ($this->_property_bag === null) {
            $bag = new PropertyBag(
                $this->_parseProperties()
            );

            $this->_cachePropertyBag($bag);
            $this->_property_bag = $bag;

            foreach ($bag as $property) {
                if($property instanceof _IntegratePackageInterface) {
                    $property->integratePackage($this);
                }
            }
        }

        return $this->_property_bag;
    }

    /**
     * Filters the property bag to only contain the provided properties.
     *
     * @author Oliver Lillie
     *
     * @param string ...$properties Must be a valid property value that is
     *  retuned from PropertyBag::getValidPropertyList().
     *
     * @return \DpkgBrowser\Classes\Dpkg\PropertyBag
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function filterPropertyBag(string ...$properties): PropertyBag {
        Args::v(
            $properties, Args::notEmpty()->each(Args::in(PropertyBag::getValidPropertyList(), true))
        );

        $bag = $this->getPropertyBag();

        $filtered_bag = [];
        foreach ($properties as $property) {
            $filtered_bag[] = $bag->{$property};
        }

        $this->_property_bag = new PropertyBag($filtered_bag);

        return $this->_property_bag;
    }

    /**
     * If the property being access exists within the list returned by
     * PropertyBag::getValidPropertyList(), then the property is attempted to
     * get accessed through the parent AccessibleProperties class.
     * Otherwise the relatedproperty object is retuned.
     *
     * @author Oliver Lillie
     *
     * @param string $property
     *
     * @return \DpkgBrowser\Classes\Dpkg\Property|mixed
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function __get(string $property) {
        if (in_array($property, PropertyBag::getValidPropertyList(), true) === false) {
            return parent::__get($property);
        }

        return $this->getPropertyBag()->{$property};
    }

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

        foreach ($this->getPropertyBag() as $property) {
            $return[$property->property] = $property->encodeForJson();
        }

        return $return;
    }

}