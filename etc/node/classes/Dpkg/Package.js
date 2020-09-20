import MagicProperties from '../MagicProperties';
import PropertyHelper from './PropertyHelper';
import PropertyBag from './PropertyBag';
import ow from "ow";
import arg from "../Arg";

/**
 * Class Package
 *
 * Contains the information about a packed that exists in the dpkg status page.
 * Until a magic property is requested the PropertBag for the object is not
 * parsed.
 *
 * @author Oliver Lillie
 *
 * @property string package_string
 * @property {Package} package
 * @property {Status} status
 * @property {Priority} priority
 * @property {Section} section
 * @property {InstalledSize} installedSize
 * @property {Maintainer} maintainer
 * @property {Architecture} architecture
 * @property {MultiArch} multiArch
 * @property {Source} source
 * @property {Version} version
 * @property {Depends} depends
 * @property {Description} description
 * @property {Homepage} homepage
 * @property {OriginalMaintainer} originalMaintainer
 * @property {Replaces} replaces
 * @property {Breaks} breaks
 * @property {Enhances} enhances
 * @property {Provides} provides
 * @property {Conflicts} conflicts
 * @property {Recommends} recommends
 * @property {Conffiles} conffiles
 * @property {Suggests} suggests
 * @property {PreDepends} preDepends
 * @property {Essential} essential
 * @property {BuiltUsing} builtUsing
 * @property {Origin} origin
 * @property {Bugs} bugs
 * @property {string} packageString
 */
class Package{

    /**
     * Package constructor.
     *
     * @access public
     * @author Oliver Lillie
     *
     * @param {string} packageString Must be a non empty string.
     */
    constructor(packageString) {
        // packageString validated through the setter

        this.packageString = packageString;

        /**
         * Contains the cached instance of the PropertyBag containing all
         * instances of the Property's in the package.
         *
         * @type {PropertyBag|null}
         * @author Oliver Lillie
         */
        this._propertyBag = null;
    }

    /**
     * The private property for the public accessor of "packageString".
     *
     * @type string
     * @author Oliver Lillie
     */
    set packageString(packageString) {
        arg(packageString, ow.string.nonEmpty);

        this._packageString = packageString;
    }
    get packageString() {
        return this._packageString;
    }

    /**
     * Returns the property objects parsed from the package string value.
     *
     * @author Oliver Lillie
     * @return {Property[]}
     */
    _parseProperties() {
        return this._packageString
            .trim()
            .split(/\n(?! )/)
            .map((line) => PropertyHelper.get(line));
    }

    /**
     * Parses the "package_string" into individual properties and then returns
     * them in a cached PropertyBag instance.
     *
     * @author Oliver Lillie
     * @return {PropertyBag}
     */
    getPropertyBag() {
        if (this._propertyBag !== null) {
            return this._propertyBag;
        }

        this._propertyBag = new PropertyBag(
            this._parseProperties()
        );

        return this._propertyBag;
    }

    /**
     * Filters the property bag to only contain the provided properties.
     *
     * @author Oliver Lillie
     *
     * @param {string[]} properties Must be a valid property value that is
     *  retuned from PropertyBag::getValidPropertyIdList().
     *
     * @return {PropertyBag}
     */
    filterPropertyBag(properties) {
        arg(properties, ow.array.nonEmpty.ofType(ow.string.nonEmpty));

        let bag = this.getPropertyBag();

        let filtered_bag = properties.map(
            (property) => bag[property]
        );

        this._propertyBag = new PropertyBag(filtered_bag);

        return this._propertyBag;
    }

    /**
     * If the property being access exists within the list returned by
     * PropertyBag.getValidPropertyIdList(), then the property is attempted to
     * get accessed through the parent AccessibleProperties class.
     * Otherwise the relatedproperty object is retuned.
     *
     * @author Oliver Lillie
     *
     * @param {string} property
     *
     * @return {Property|null}
     */
    __get (property) {
        arg(property, ow.string.nonEmpty);

        if (PropertyHelper.getValidPropertyIdList().indexOf(property) === -1) {
            return null;
        }

        const bag = this.getPropertyBag();
        
        return bag[property];
    }

    /**
     * Must return a simple object so it can be encoded into json.
     *
     * @author Oliver Lillie
     * @return {Object}
     */
    encodeForJson() {
        let encoded = {};
        
        this.getPropertyBag().map(
            (property) => encoded[property.property] = property.encodeForJson()
        );

        return encoded;
    }

}

module.exports = MagicProperties(Package);