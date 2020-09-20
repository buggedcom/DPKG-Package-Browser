import Parser from './Dpkg/Parser';
import PackageCollection from './Dpkg/PackageCollection';
import Package from './Dpkg/Package';
import PackageVersionWithAlternatives from './Dpkg/PackageVersionWithAlternatives';
import PackageVersion from './Dpkg/PackageVersion';
import Config from './Config';
import ow from 'ow';
import arg from './Arg';

// singleton cache in module memory so it doesn't need to be recreated betweem
// requests is a performance improvement.
let _instance = null;

/**
 * Class Dpkg
 *
 * This is the base dpkg status parser.
 *
 * @author Oliver Lillie
 * @package Reaktor\Classes
 */
class Dpkg extends Parser {

    /**
     * Dpkg constructor.
     *
     * @access public
     * @author Oliver Lillie
     *
     * @param {Config} config The configuration instance.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    constructor(config) {
        arg(config, ow.object.hasKeys('dpkp_status_path'));

        super(config.dpkp_status_path);
    }

    /**
     * Creates a static cache of a configured instance of the Dkpg parser.
     *
     * @author Oliver Lillie
     * @return {*}
     */
    static instance() {
        if(_instance !== null) {
            return _instance;
        }

        _instance = new Dpkg(
            Config.instance()
        );

        return _instance;
    }

    /**
     * Calculates the dependants (ie other packages that use the given package)
     * for the given package.
     *
     * @author Oliver Lillie
     *
     * @param {Package} pkg
     *
     * @return {PackageCollection}
     */
    getDependantsForPackage(pkg) {
        arg(pkg, ow.object.instanceOf(Package));

        const packageName = pkg.package.value;

        let dependants = [];
        this._manifest.forEach(
            (otherPackage) => {
                this._getDependenciesForPackage(otherPackage).some(
                    (depency) => {
                        if(depency.packageName === packageName) {
                            dependants.push(otherPackage);
                            return true;
                        }
                    }
                );
            }
        );

        return new PackageCollection(dependants);
    }

    /**
     * Returns a map of package names to list of packages they provide.
     *
     * @author Oliver Lillie
     * @return array
     */
    getPackageToProvidedMap() {
        let mapping = [];
        this._manifest.forEach(
            (pkg) => {
                if('provides' in pkg) {
                    return;
                }

                let packageName = pkg.package.value;
                pkg.provides.value.forEach(
                    (pkgVersion) => {
                        if(pkgVersion instanceof PackageVersionWithAlternatives === true) {
                            pkgVersion.forEach((altPackageVersion) => mapping[altPackageVersion.package] = packageName);
                        } else {
                            mapping[pkgVersion.package] = packageName;
                        }
                    }
                );
            }
        );

        return mapping;
    }

    /**
     * Returns a list of dependancy PackageVersion objects related to the
     * provided package.
     *
     * @author Oliver Lillie
     *
     * @param {Package} pkg
     *
     * @return {PackageVersion[]}
     */
    _getDependenciesForPackage(pkg) {
        arg(pkg, ow.object.instanceOf(Package));

        let dependancies = [];

        if(pkg.depends.value instanceof Array === true) {
            dependancies = dependancies.concat(this._getFlattenedDependants(pkg.depends.value));
        }
        if(pkg.preDepends.value instanceof Array === true) {
            dependancies = dependancies.concat(this._getFlattenedDependants(pkg.preDepends.value));
        }

        return dependancies;
    }

    /**
     * Flattens dependancy values since some may be instances of
     * PackageVersionWithAlternatives.
     *
     * @author Oliver Lillie
     *
     * @param {PackageVersionWithAlternatives[]|PackageVersion[]} depends
     *
     * @return {PackageVersion[]}
     */
    _getFlattenedDependants(depends) {
        arg(depends, ow.array.ofType(
            ow.any(
                ow.object.instanceOf(PackageVersionWithAlternatives),
                ow.object.instanceOf(PackageVersion)
            )
        ));

        let flattened = [];
        depends.forEach(
            (depend) => {
                if(depend instanceof PackageVersionWithAlternatives === true) {
                    flattened = flattened.concat(depend.toArray());
                } else {
                    flattened.push(depend);
                }
            }
        );
        return flattened;
    }
}

module.exports = Dpkg;