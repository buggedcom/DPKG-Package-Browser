import fs from 'fs';
import Package from './Package';
import Filterable from './Filterable';
import PackageCollection from './PackageCollection';
import ow from "ow";
import arg from "../Arg";

/**
 * Class Parser
 *
 * The class responsible for parsing the dpkg/status file.
 *
 * @author Oliver Lillie
 * @property {string} path
 */
class Parser extends Filterable {

    /**
     * Parser constructor.
     *
     * @access public
     * @author Oliver Lillie
     *
     * @param {string} path The path to the file that is to be parsed.
     */
    constructor(path) {
        // path is validated through the path setter

        super();

        this.path = path;

        this.parse();
    }

    /**
     * @inheritDoc
     * @return {PackageCollection}
     */
    getConstructor() {
        // this is correct since this is called from a filter to return a
        // subset and therefore we don't want the Parser constructor.
        return PackageCollection;
    }

    /**
     * The path to the dpkg status file.
     *
     * @type string
     * @author Oliver Lillie
     */
    set path(path) {
        arg(path, ow.string.nonEmpty);

        this._path = path;
    }

    get path() {
        return this._path;
    }

    /**
     * Kicks of the parsing of the status file.
     *
     * @author Oliver Lillie
     * @return {Parser}
     */
    parse() {
        // check to see if the manifest already exists or has a cached version
        // available. If there is, don't do any further parsing.
        if(this._manifest.length > 0)  {
            return this;
        }

        const packages = fs.readFileSync(
            this._path,
            'utf-8'
        )
        .trim()
        .split(/\n\n/)
        .map((packageString) => new Package(packageString));

        this.setManifest(packages);

        return this;
    }

    /**
     * Returns a simply array of names of the packages contained in the
     * manifest.
     *
     * @author Oliver Lillie
     * @return {string[]}
     */
    getPackageList() {
        return this._manifest.map((pkg) => pkg.package.value);
    }

}

module.exports = Parser;