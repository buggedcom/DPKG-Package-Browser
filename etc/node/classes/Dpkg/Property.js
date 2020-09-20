import ow from "ow";
import arg from "../Arg";

/**
 * Class Property
 *
 * The base property class that is used for package properties.
 *
 * @author Oliver Lillie
 * @property {string} propertyString
 * @property {string} property
 * @property {string|null} value
 */
class Property {

    /**
     * Property constructor.
     *
     * @access public
     * @author Oliver Lillie
     *
     * @param {string} propertyString
     * @param {string} property
     * @param {string|null} value
     *
     * @throws \InvalidArgumentException
     */
    constructor(propertyString, property, value) {
        // propertyString, property and value are validated through the setters.

        this.propertyString = propertyString;
        this.property = property;
        this.value = value;
    }

    /**
     * The private property for the public accessor of "propertyString".
     *
     * @type string
     * @author Oliver Lillie
     */
    set propertyString(propertyString) {
        arg(propertyString, ow.string.nonEmpty);

        this._propertyString = propertyString;
    }

    get propertyString() {
        return this._propertyString;
    }

    /**
     * The private property for the public accessor of "property".
     *
     * @type string
     * @author Oliver Lillie
     */
    set property(property) {
        arg(property, ow.string.nonEmpty);

        this._property = property;
    }

    get property() {
        return this._property;
    }

    /**
     * The private property for the public accessor of "value".
     *
     * @type null|string|array
     * @author Oliver Lillie
     */
    set value(value) {
        arg(value, ow.any(
            ow.null,
            ow.string,
            ow.array
        ));

        this._value = value;
    }

    get value() {
        return this._value;
    }

    /**
     * Must return a simple object so it can be encoded into json.
     *
     * @author Oliver Lillie
     * @return {string|null}
     */
    encodeForJson() {
        return this._value;
    }

}

module.exports = Property;