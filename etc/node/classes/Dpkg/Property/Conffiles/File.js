import ow from "ow";
import arg from "../../../Arg";

/**
 * Class File
 *
 * Holds the file value from the Conffiles list.
 *
 * @author Oliver Lillie
 * @property {string} file
 * @property {string} hash
 */
class File {

    /**
     * Property constructor.
     *
     * @access public
     * @author Oliver Lillie
     *
     * @param {string} file
     * @param {string} hash
     */
    constructor(file, hash) {
        // file and hash validated through setters.

        this.file = file;
        this.hash = hash;
    }

    /**
     * The private property for the public accessor of "file".
     *
     * @type {string}
     * @author Oliver Lillie
     */
    set file(file) {
        arg(file, ow.string.nonEmpty);

        this._file = file;
    }

    get file() {
        return this._file;
    }

    /**
     * The private property for the public accessor of "hash".
     *
     * @type {string|null}
     * @author Oliver Lillie
     */
    set hash(hash) {
        arg(hash, ow.any(
            ow.string.nonEmpty,
            ow.null
        ));

        this._hash = hash;
    }

    get hash() {
        return this._hash;
    }

    /**
     * Must return a simple object so it can be encoded into json.
     *
     * @author Oliver Lillie
     * @return {{file: string, hash: string}}
     */
    encodeForJson() {
        return {
            file: this._file,
            hash: this._hash
        };
    }

}

module.exports = File;
