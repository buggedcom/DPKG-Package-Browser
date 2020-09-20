import _List from './_List';
import PackageVersion from '../PackageVersion';
import PackageVersionWithAlternatives from '../PackageVersionWithAlternatives';
import ow from "ow";
import arg from "../../Arg";

/**
 * Class _HasPackageVersion
 *
 * Post processes list based properties so that each item in the list is an
 * instance of \Reaktor\Classes\Dpkg\PackageVersion.
 *
 * @author Oliver Lillie
 * @property {PackageVersion[]} value
 */
class _HasPackageVersion extends _List {

    /**
     * Post processes the item in the list and returns an instance of
     * PackageVersion or PackageVersionWithAlternatives if the versions are a
     * piped separated list of alternatives.
     *
     * @author Oliver Lillie
     *
     * @param {string} item The list item to post process.
     *
     * @return {PackageVersion|PackageVersionWithAlternatives}
     */
    _postProcessListItem(item) {
        arg(item, ow.string.nonEmpty);

        // check if the package is simple instance, or if it contains
        // alternatives which are pipe `|` separated
        const matches = item.split(/\s*\|\s*/);
        if(matches && matches.length > 1) {
            const alternatives = matches.map((dependancyString) => new PackageVersion(dependancyString));
            return new PackageVersionWithAlternatives(alternatives);
        }

        return new PackageVersion(item);
    }

    /**
     * Must return a simple object so it can be encoded into json.
     *
     * @author Oliver Lillie
     * @return {Object[]}
     */
    encodeForJson() {
        return this._value.map((item) => {
            return item.encodeForJson()
        });
    }
    
}

module.exports = _HasPackageVersion;
