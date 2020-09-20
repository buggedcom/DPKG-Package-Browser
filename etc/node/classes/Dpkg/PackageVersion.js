import ow from "ow";
import arg from "../Arg";

/**
 * Class PackageVersion
 *
 * The property class used when the property value contains a list of packages
 * and optionaly package versions.
 *
 * @author Oliver Lillie
 * @property {string} dependancyString
 * @property {string} packageName
 * @property {string} version
 */
class PackageVersion {

    /**
     * PackageVersion constructor.
     *
     * @access public
     * @author Oliver Lillie
     *
     * @param {string} dependancyString The string of the dependancy list.
     */
    constructor(dependancyString) {
        // dependancyString validated through the setter

        this.dependancyString = dependancyString;
        this.packageName = null;
        this.version = null;

        this.parse();
    }

    /**
     * The private property for the public accessor of "dependancyString".
     *
     * @type {string}
     * @author Oliver Lillie
     */
    set dependancyString(dependancyString) {
        arg(dependancyString, ow.string.nonEmpty);

        this._dependancyString = dependancyString;
    }

    get dependancyString() {
        return this._dependancyString;
    }

    /**
     * The private property for the public accessor of "packageName".
     *
     * @type {null|string}
     * @author Oliver Lillie
     */
    set packageName(packageName) {
        arg(packageName, ow.any(
            ow.string.nonEmpty,
            ow.null
        ));

        this._packageName = packageName;
    }

    get packageName() {
        return this._packageName;
    }

    /**
     * The private property for the public accessor of "version".
     *
     * @type {null|string}
     * @author Oliver Lillie
     */
    set version(version) {
        arg(version, ow.any(
            ow.string.nonEmpty,
            ow.null
        ));

        this._version = version;
    }

    get version() {
        return this._version;
    }

    /**
     * Parses the dependancy_string value to capture the package and version
     * strings.
     *
     * @author Oliver Lillie
     * @return void
     */
    parse() {
        const matches = this._dependancyString.match(/^(.+) \((.+)\)$/);
        if(matches && matches.length > 0) {
            this.packageName = matches[1];
            this.version = matches[2];
        } else {
            this.packageName = this._dependancyString;
        }
    }

    /**
     * Must return a simple object so it can be encoded into json.
     *
     * @author Oliver Lillie
     * @return {{packageName: string, version: string}}
     */
    encodeForJson() {
        return {
            packageName: this._packageName,
            version: this._version
        };
    }

}

module.exports = PackageVersion;