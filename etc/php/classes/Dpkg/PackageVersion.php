<?php

namespace DpkgBrowser\Classes\Dpkg;

use ArrayAccess;
use CaseConverter\CaseString;
use Countable;
use Iterator;
use DpkgBrowser\Classes\AccessibleProperties;
use DpkgBrowser\Classes\Args;
use DpkgBrowser\Classes\JsonPayloadDataInterface;
use DpkgBrowser\Traits\ArrayContainer;

/**
 * Class PackageVersion
 *
 * The property class used when the property value contains a list of packages
 * and optionaly package versions. 
 *
 * @author Oliver Lillie
 * @package DpkgBrowser\Classes\Dpkg
 * @property string $dependancy_string
 * @property string $package_name
 * @property string $version
 */
class PackageVersion extends AccessibleProperties implements JsonPayloadDataInterface {

    /**
     * The private property for the public accessor of "dependancy_string".
     *
     * @type string
     * @author Oliver Lillie
     */
    protected $_dependancy_string;

    /**
     * The private property for the public accessor of "package_name".
     *
     * @type string
     * @author Oliver Lillie
     */
    protected $_package_name;

    /**
     * The private property for the public accessor of "version".
     *
     * @type null|string
     * @author Oliver Lillie
     */
    protected $_version;

    /**
     * PackageVersion constructor.
     *
     * @access public
     * @author Oliver Lillie
     *
     * @param string $dependancy_string The string of the dependancy list.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $dependancy_string) {
        $this->_setAccessibleProperties(
            [
                'dependancy_string',
                'package_name',
                'version',
            ]
        );

        $this->dependancy_string = $dependancy_string;

        $this->parse();
    }

    /**
     * The setter for setting the "dependancy_string" value.
     *
     * @author Oliver Lillie
     *
     * @param string $dependancy_string Must be a non empty string.
     *
     * @return \DpkgBrowser\Classes\Dpkg\PackageVersion
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \ReflectionException
     */
    public function setDependancyString(string $dependancy_string): self {
        Args::v(
            $dependancy_string, Args::notEmpty()
        );

        $this->_dependancy_string = $dependancy_string;

        return $this;
    }

    /**
     * The setter for setting the "package" value.
     *
     * @author Oliver Lillie
     *
     * @param string $package Must be a non empty string.
     *
     * @return \DpkgBrowser\Classes\Dpkg\PackageVersion
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \ReflectionException
     */
    public function setPackageName(string $package): self {
        Args::v(
            $package, Args::notEmpty()
        );

        $this->_package_name = $package;

        return $this;
    }

    /**
     * The setter for setting the "version" value.
     *
     * @author Oliver Lillie
     *
     * @param string $version Must be a non empty string.
     *
     * @return \DpkgBrowser\Classes\Dpkg\PackageVersion
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \ReflectionException
     */
    public function setVersion(string $version): self {
        Args::v(
            $version, Args::notEmpty()
        );

        $this->_version = $version;

        return $this;
    }

    /**
     * Parses the dependancy_string value to capture the package and version
     * strings.
     *
     * @author Oliver Lillie
     * @return void
     */
    public function parse(): void {
        if (preg_match('/^(.+) \((.+)\)$/', $this->_dependancy_string, $matches) > 0) {
            $this->package_name = $matches[1];
            $this->version = $matches[2];
        } else {
            $this->package_name = $this->_dependancy_string;
        }
    }

    /**
     * @inheritdoc
     * @author Oliver Lillie
     * @return array
     */
    public function encodeForJson(): array {
        // different case set in package_name vs packageName because the js
        // frontend is camels.
        return [
            'packageName' => $this->_package_name,
            'version' => $this->_version,
        ];
    }

}