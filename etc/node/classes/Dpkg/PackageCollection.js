import Filterable from './Filterable';

/**
 * class PackageCollection
 *
 * A container for a list of packages.
 *
 * @author Oliver Lillie
 */
class PackageCollection extends Filterable {

    /**
     * @inheritDoc
     * @return {PackageCollection}
     */
    getConstructor() {
        return PackageCollection;
    }

    /**
     * Must return a simple object so it can be encoded into json.
     *
     * @author Oliver Lillie
     * @return {Object[]}
     */
    encodeForJson()  {
        return this._manifest.map(
            (pkg) => pkg.encodeForJson()
        );
    }
}

module.exports = PackageCollection;
