import ow from 'ow';
import arg from './Arg';

/**
 * Class ArrayIterator
 *
 * Used as a base class to provide ES6 object iteration.
 *
 * @author Oliver Lillie
 * @property {array} _manifest A container for the iterative data.
 */
class ArrayIterator {

    constructor(manifest) {
        // manifest validated through setManifest

        this.setManifest(manifest);
    }

    /**
     * Sets the data that the class will iterate over.
     *
     * Once set it also updates the objects length property.
     *
     * @author Oliver Lillie
     * @param {Array|undefined} manifest
     */
    setManifest(manifest) {
        arg(manifest, ow.any(
            ow.array,
            ow.undefined
        ));

        this._manifest = manifest || [];
        this.length = this._manifest.length;
    }

    /**
     * Alias for Array.prototype.forEach functionality.
     *
     * @author Oliver Lillie
     * @param {Function} callback
     */
    forEach(callback) {
        arg(callback, ow.function);

        this._manifest.forEach(callback);
    }

    /**
     * Alias for Array.prototype.some functionality.
     *
     * @author Oliver Lillie
     * @param {Function} callback
     */
    some(callback) {
        arg(callback, ow.function);

        return this._manifest.some(callback);
    }

    /**
     * Alias for Array.prototype.map functionality.
     *
     * @author Oliver Lillie
     * @param {Function} callback
     */
    map(callback) {
        arg(callback, ow.function);

        return this._manifest.map(callback);
    }

    /**
     * Alias for Array.prototype.filter functionality.
     *
     * @author Oliver Lillie
     * @param {Function} callback
     */
    filter(callback) {
        arg(callback, ow.function);

        return this._manifest.filter(callback);
    }

    /**
     * Alias for Array.prototype.find functionality.
     *
     * @author Oliver Lillie
     * @param {Function} callback
     */
    find(callback) {
        arg(callback, ow.function);

        return this._manifest.find(callback);
    }

    /**
     * Provides access to the given index of the manifest.
     *
     * @author Oliver Lillie
     * @param {number} index
     */
    offsetGet(index) {
        arg(index, ow.number);

        return this._manifest[index] || null;
    }

    /**
     * Returns the objects manifest.
     *
     * @author Oliver Lillie
     * @return {array}
     */
    toArray() {
        return this._manifest;
    }

    /**
     * Provides the objects iterative functionality.
     *
     * @author Oliver Lillie
     * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Symbol/iterator
     */
    [Symbol.iterator]() {
        let index = -1;
        let data  = this._manifest;

        return {
            next: () => ({ value: data[++index], done: !(index in data) })
        };
    };

}

module.exports = ArrayIterator;