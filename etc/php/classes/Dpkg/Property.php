<?php

namespace DpkgBrowser\Classes\Dpkg;

use CaseConverter\CaseString;
use DpkgBrowser\Classes\Args;
use DpkgBrowser\Classes\JsonPayloadDataInterface;
use RuntimeException;
use DpkgBrowser\Classes\AccessibleProperties;

/**
 * Class Property
 *
 * The base property class that is used for package properties.
 *
 * @author Oliver Lillie
 * @package DpkgBrowser\Classes\Dpkg
 * @property string property_string
 * @property string property
 * @property string value
 */
class Property extends AccessibleProperties implements JsonPayloadDataInterface {

    /**
     * The private property for the public accessor of "property_string".
     *
     * @type string
     * @author Oliver Lillie
     */
    protected $_property_string;

    /**
     * The private property for the public accessor of "property".
     *
     * @type string
     * @author Oliver Lillie
     */
    protected $_property;

    /**
     * The private property for the public accessor of "value".
     *
     * @type null|string|array
     * @author Oliver Lillie
     */
    protected $_value;

    /**
     * Property constructor.
     *
     * @access public
     * @author Oliver Lillie
     *
     * @param string $property_string
     * @param string $property
     * @param string|null $value
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $property_string, string $property, ?string $value) {
        $this->_setAccessibleProperties(
            [
                'property_string',
                'property',
                'value'
            ]
        );

        $this->property_string = $property_string;
        $this->property = $property;
        $this->value = $value;
    }

    /**
     * Creates a property object from the property string.
     *
     * @author Oliver Lillie
     *
     * @param string $property_string Must be a non-empty string.
     *
     * @return \DpkgBrowser\Classes\Dpkg\Property
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public static function get(string $property_string): Property {
        Args::v(
            $property_string, Args::notEmpty()
        );

        [$property, $value] = preg_split('/:( |\n)/', $property_string, 2);

        $allowed_properties = [
            'Package', 'Status', 'Priority', 'Section', 'Installed-Size', 'Maintainer', 'Architecture', 'Multi-Arch',
            'Source', 'Version', 'Depends', 'Description', 'Homepage', 'Original-Maintainer', 'Replaces', 'Breaks',
            'Enhances', 'Provides', 'Conflicts', 'Recommends', 'Conffiles', 'Suggests', 'Pre-Depends', 'Essential',
            'Built-Using', 'Origin', 'Bugs'
        ];
        if (in_array($property, $allowed_properties, true) === false) {
            throw new RuntimeException('Unexpected property "' . $property . '" found from property string: ' . $property_string);
        }

        $class = '\DpkgBrowser\Classes\Dpkg\Property\\' . CaseString::kebab($property)->pascal();

        return new $class($property_string, CaseString::kebab(strtolower($property))->camel(), $value);
    }

    /**
     * The setter for setting the "property_string" value.
     *
     * @author Oliver Lillie
     *
     * @param string $property_string Must be a non-empty string value.
     *
     * @return \DpkgBrowser\Classes\Dpkg\Property
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \ReflectionException
     */
    public function setPropertyString(string $property_string): self {
        Args::v(
            $property_string, Args::notEmpty()
        );

        $this->_property_string = $property_string;

        return $this;
    }

    /**
     * The setter for setting the "property" value.
     *
     * @author Oliver Lillie
     *
     * @param string $property Must be a non-empty camel case string.
     *
     * @return \DpkgBrowser\Classes\Dpkg\Property
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \ReflectionException
     */
    public function setProperty(string $property): self {
        Args::v(
            $property, Args::notEmpty()->callback(function ($value) {
                return CaseString::camel($value)->camel() === $value;
            })
        );

        $this->_property = $property;

        return $this;
    }

    /**
     * The setter for setting the "value" value.
     *
     * @author Oliver Lillie
     *
     * @param string|null $value Must be null or a string value.
     *
     * @return self
     */
    public function setValue(?string $value): Property {
        $this->_value = $value;

        return $this;
    }

    /**
     * @inheritdoc
     * @author Oliver Lillie
     * @return mixed|string
     */
    public function encodeForJson() {
        return $this->value;
    }

    /**
     * @author Oliver Lillie
     * @return string
     */
    public function __toString() {
        return (string)$this->_value;
    }

}