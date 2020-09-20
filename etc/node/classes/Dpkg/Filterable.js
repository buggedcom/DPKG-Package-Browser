import ArrayIterator from '../ArrayIterator';
import ow from "ow";
import arg from "../Arg";

/**
 * Class Filterable
 *
 * Adds filter ability to the classes that extends it.
 *
 * @author Oliver Lillie
 */
class Filterable extends ArrayIterator {

    /**
     * Used to determine the collection class that is to be returned from any
     * call to Filterable.filter.
     *
     * @author Oliver Lillie
     * @return {Filterable}
     */
    getConstructor() {
        return Filterable;
    }

    /**
     * Allows filterng of the current property maifest and then returns a new
     * PropertyCollection with the filtered properties.
     *
     * It does not update the current manifest.
     *
     * @author Oliver Lillie
     *
     * @param {string} property
     * @param {string} value
     * @param {bool} strict
     *
     * @return {PackageCollection|Parser|Filterable}
     */
    filter(property, value, strict) {
        arg(property, ow.string.nonEmpty);
        arg(value, ow.string);
        arg(strict, ow.boolean);

        const filtered = this._manifest.filter(
            (pkg) => {
                if(
                    (strict === true && pkg[property].value === value)
                    || (strict === false && pkg[property].value.indexOf(value) >= 0)
                ) {
                    return true;
                }
            }
        );

        const cls = this.getConstructor();
        return new cls(filtered);
    }

    /**
     * Filters the property bag of all the packages contained in the manifest to
     * only contain the provided properties.
     *
     * @author Oliver Lillie
     *
     * @param {string[]} properties Must be a valid property value that is
     *  returned from PropertyHelper.getValidPropertyIdList().
     *
     * @return {PackageCollection|Parser|Filterable}
     */
    filterPropertyBags(properties) {
        arg(properties, ow.array.nonEmpty.ofType(ow.string));

        // it is important to clone these and return a new collection because
        // the Dpkg instance is statically cached inside the module memory and
        // cannot be altered because it is altered the static cache is altered
        // and that means different data on different requests. Which in turn
        // leads to much head scratching of wondering what on earth is
        // happening.
        const filteredClones = this._manifest.map((pkg) => {
            const clone = Object.assign( Object.create( Object.getPrototypeOf(pkg)), pkg);
            return clone.filterPropertyBag(properties)
        });

        const cls = this.getConstructor();
        return new cls(filteredClones);
    }

}

module.exports = Filterable;
