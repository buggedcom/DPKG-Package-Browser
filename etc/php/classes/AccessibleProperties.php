<?php

namespace DpkgBrowser\Classes;

use BadMethodCallException;
use CaseConverter\CaseString;
use InvalidArgumentException;
use ReflectionMethod;
use RuntimeException;

/**
 * The AccessibleProperties class allows the definition of protected properties
 * that are accessible, however have to adhere to strict argument validation.
 *
 * @author Oliver Lillie
 */
class AccessibleProperties {

    /**
     * Contains a list of accessible properties that can be set.
     *
     * @author Oliver Lillie
     * @var array
     * @access protected
     */
    protected $_accessible_properties;

    /**
     * Converts snakecase (snake_case) or dashcase (dash-case) into camelcase
     * (camelCase)
     *
     * @author Oliver Lillie
     * @access protected
     *
     * @param string $string
     *
     * @return string
     */
    protected function _toCamelCase($string): string {
        return CaseString::snake($string)->camel();
    }

    /**
     * Returns a boolean value if the property exists on the object.
     *
     * @access public
     * @author: Oliver Lillie
     *
     * @param  string $property The property name to check for.
     *
     * @return boolean Returns true if the object has the related property.
     */
    public function hasProperty($property): bool {
        return in_array($property, $this->_accessible_properties, true);
    }

    /**
     * Sets an array of accessible properties. Each property should exist on the
     * class as a protected variable. Each accessible property must have an
     * associated set function or else the setting of the public version of the
     * property will throw and BadMethodCallException.
     *
     * @author Oliver Lillie
     * @access protected
     *
     * @param array $accessible_properties
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function _setAccessibleProperties($accessible_properties): void {
        if ($this->_accessible_properties === null) {
            $this->_accessible_properties = [];
        }

        if (is_array($accessible_properties) === false) {
            throw new InvalidArgumentException('The `$accessible_properties` argument in the constructor must be an array');
        }

        $this->_accessible_properties = array_merge($this->_accessible_properties, $accessible_properties);
    }

    /**
     * Sets one of the properties set via
     * AccessibleProperties::_setAccessibleProperties. This function sets the
     * property by calling the camelcase version of the preoperty name prefixed
     * by set. For example if setting the property "response_meta" the function
     * called would be setResponseMeta.
     *
     * @author Oliver Lillie
     * @access public
     *
     * @param string $property
     * @param mixed $value
     *
     * @return void
     * @throws \BadMethodCallException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function __set(string $property, $value) {
        $this->__isset($property);

        $function = $this->_toCamelCase('set_' . $property);

        if (is_callable([$this, $function]) === false) {
            throw new BadMethodCallException('The property `' . $property . '` is write accessible, however the modifier method callback `' . $function . '` is not.');
        }

        $reflection = new ReflectionMethod($this, $function);
        if ($reflection->isPublic() === false) {
            throw new RuntimeException('The property `' . $property . '` is write accessible, and the modifier method exists but it is not a public function.');
        }

        $this->$function($value);
    }

    /**
     * Determines if the property exists within the AccessibleProperties object.
     *
     * @author Oliver Lillie
     *
     * @param string $property
     *
     * @return void
     * @throws \BadMethodCallException
     */
    public function __isset(string $property) {
        if ($this->hasProperty($property) === false) {
            throw new BadMethodCallException('The property `' . $property . '` is not write accessible.');
        }
    }

    /**
     * Gets and returns an accessible property value. If the property is not
     * accessible a BadMethodCallException is thrown.
     *
     * @author Oliver Lillie
     * @access public
     *
     * @param string $property
     *
     * @return mixed
     * @throws \BadMethodCallException
     */
    public function __get(string $property) {
        $this->__isset($property);

        if (property_exists($this, '_' . $property) === false) {
            throw new BadMethodCallException('The property `' . $property . '` does not exist on the object `' . get_class($this) . '`.');
        }

        return $this->{'_' . $property};
    }

}
