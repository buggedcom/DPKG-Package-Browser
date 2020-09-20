import Property from '../Property';
import ow from "ow";
import arg from "../../Arg";

/**
 * Class _List
 *
 * Processes list based properties so that each item in the list is a string.
 *
 * @author Oliver Lillie
 * @property {string[]} value
 */
class _List extends Property {

    getDelimiter() {
        return /,\s/;
    }

    /**
     * Post processes the item in the list and returns an instance of
     * PackageVersion.
     *
     * @author Oliver Lillie
     *
     * @param {string} item The list item to post process.
     *
     * @return {string}
     */
    _postProcessListItem(item) {
        arg(item, ow.string.nonEmpty);

        return item;
    }

    /**
     * If a value is provided then the value is split and each is processed
     * through _List._postProcessListItem to allow child classes to process the
     * value.
     *
     * @author Oliver Lillie
     * @type {string[]}
     */
    set value(value) {
        arg(value, ow.any(
            ow.null,
            ow.string.nonEmpty
        ));

        if(value !== null) {
            value = value.split(this.getDelimiter()).map((item) => this._postProcessListItem(item));
        } else {
            value = [];
        }

        super.value = value;
    }

    get value() {
        return super.value;
    }

}

module.exports = _List;
