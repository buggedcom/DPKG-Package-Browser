import _List from './_List';
import File from './Conffiles/File';
import ow from "ow";
import arg from "../../Arg";

/**
 * Class Conffiles
 *
 * Holds the package "Conffiles" property.
 *
 * @author Oliver Lillie
 * @property {{path, hash}} value
 */
class Conffiles extends _List {

    getDelimiter() {
        return /\n\s*/;
    }

    _postProcessListItem(item) {
        arg(item, ow.string.nonEmpty);

        const [path, hash] = item.trim().split(' ');

        return new File(path.trim(), hash.trim());
    }
}

module.exports = Conffiles;
