<?php

namespace DpkgBrowser\Classes\Dpkg;

use ArrayAccess;
use CaseConverter\CaseString;
use Countable;
use Iterator;
use DpkgBrowser\Classes\Args;
use DpkgBrowser\Traits\ArrayContainer;

/**
 * Class PropertyBag
 *
 * A container class that allows iteration over the packages properties.
 *
 * @author Oliver Lillie
 * @package DpkgBrowser\Classes\Dpkg
 *
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
class PropertyBag implements ArrayAccess, Iterator, Countable {

    use ArrayContainer;

    /**
     * The data container from ArrayContainer.
     *
     * @type \DpkgBrowser\Classes\Dpkg\Property[]
     * @author Oliver Lillie
     */
    private $_manifest;

    /**
     * Returns a list of valid properties.
     *
     * @var array
     * @author Oliver Lillie
     * @return array
     */
    public static function getValidPropertyList(): array {
        return [
            'package', 'status', 'priority', 'section', 'installedSize', 'maintainer', 'architecture', 'multiArch',
            'source', 'version', 'depends', 'description', 'homepage', 'originalMaintainer', 'replaces', 'breaks',
            'enhances', 'provides', 'conflicts', 'recommends', 'conffiles', 'suggests', 'preDepends', 'essential',
            'builtUsing', 'origin', 'bugs'
        ];
    }

    /**
     * Handles the dynamic property access.
     *
     * If the property does not exist within the property bag then the property
     * object is created with a null value.
     *
     * @author Oliver Lillie
     *
     * @param string $property
     *
     * @return \DpkgBrowser\Classes\Dpkg\Property
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \ReflectionException
     */
    public function __get(string $property) {
        Args::v(
            $property, Args::in(self::getValidPropertyList(), true)
        );

        foreach ($this->_manifest as $prop) {
            if ($prop->property === $property) {
                return $prop;
            }
        }

        $class = '\DpkgBrowser\Classes\Dpkg\Property\\' . CaseString::camel($property)->pascal();

        return new $class(ucfirst(CaseString::camel($property)->kebab()) . ': ', $property, null);
    }

    /**
     * Determines if the property exists on the property bag.
     *
     * @author Oliver Lillie
     *
     * @param string $property
     *
     * @return bool
     */
    public function __isset(string $property) {
        return in_array($property, self::getValidPropertyList(), true);
    }

    /**
     * Prevents properties from being set.
     *
     * @author Oliver Lillie
     *
     * @param string $property
     * @param null $value Ignored.
     *
     * @return void
     * @throws \LogicException
     */
    public function __set(string $property, $value) {
        throw new \LogicException('You cannot set values to \DpkgBrowser\Classes\Dpkg\PropertyBag.');
    }

}