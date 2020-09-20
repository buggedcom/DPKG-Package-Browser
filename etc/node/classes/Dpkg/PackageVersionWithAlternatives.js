import ArrayIterator from '../ArrayIterator';

/**
 * Class PackageVersionWithAlternatives
 *
 * A container for a list of PackageVersions that are alternatives to each
 * other.
 *
 * @author Oliver Lillie
 */
class PackageVersionWithAlternatives extends ArrayIterator {
    /**
     * Must return a simple object so it can be encoded into json.
     *
     * @author Oliver Lillie
     * @return {(*|Array|Object[]|{packageName: string, version: string}|{}|{address: string, display: string})[]}
     */
    encodeForJson() {
        return this._manifest.map((packageVersion) => packageVersion.encodeForJson());
    }
}

module.exports = PackageVersionWithAlternatives;