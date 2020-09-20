import PropertyClasses from './PropertyClasses';
import RuntimeError from '../RuntimeError';
import Case from 'case';
import StrTrim from 'str-trim';
import ow from 'ow';
import arg from '../Arg';

/**
 * Helper functions for accessing properties.
 *
 * @author Oliver Lillie
 */
export default {

    /**
     * Returns a list of valid property ids.
     *
     * @author Oliver Lillie
     * @return {string[]}
     */
    getValidPropertyIdList() {
        return [
            'package', 'status', 'priority', 'section', 'installedSize', 'maintainer', 'architecture', 'multiArch',
            'source', 'version', 'depends', 'description', 'homepage', 'originalMaintainer', 'replaces', 'breaks',
            'enhances', 'provides', 'conflicts', 'recommends', 'conffiles', 'suggests', 'preDepends', 'essential',
            'builtUsing', 'origin', 'bugs'
        ];
    },

    /**
     * Returns a list of valid property labels.
     *
     * @author Oliver Lillie
     * @return {string[]}
     */
    getValidPropertyNameList() {
        return [
            'Package', 'Status', 'Priority', 'Section', 'Installed-Size', 'Maintainer', 'Architecture', 'Multi-Arch',
            'Source', 'Version', 'Depends', 'Description', 'Homepage', 'Original-Maintainer', 'Replaces', 'Breaks',
            'Enhances', 'Provides', 'Conflicts', 'Recommends', 'Conffiles', 'Suggests', 'Pre-Depends', 'Essential',
            'Built-Using', 'Origin', 'Bugs'
        ];
    },

    /**
     * Performs a proper limited split of a string. For example:
     *
     * this.splitLimited("abc def ghi", " ", 2)
     * // => ["abc", "def ghi"]
     *
     * @author Oliver Lillie
     * @param {string} str The string to split.
     * @param {RegExp|string} delimiter The delimiter that will split the
     *  string.
     * @param {int} limit The number of parts required in the split.
     * @param {string|null} joiner If not provided and `delimeter` is a string
     *  then any splits after the limit will be joined back together with the
     *  delimiter. Otherwise this value will be used.
     * @return {string[]}
     */
    splitLimited(str, delimiter, limit, joiner) {
        arg(str, ow.string);
        arg(delimiter, ow.any(
            ow.regExp,
            ow.string
        ));
        arg(limit, ow.number);
        arg(joiner, ow.any(
            ow.string,
            ow.null,
            ow.undefined
        ));

        let parts = str.split(delimiter);

        limit--;

        if(parts.length > limit) {
            let pieces = parts.splice(0, limit);
            pieces.push(
                parts.join(joiner || delimiter)
            );

            return pieces;
        }

        return parts;
    },

    /**
     * Static property creator method that creates a properties instance of the
     * properties label.
     *
     * @author Oliver Lillie
     * @param {string} propertyString The label and value string. ie
     *  Package: package-name
     *
     * @return {Package|Status|Priority|Section|InstalledSize|Maintainer|Architecture|MultiArch|Source|Version|Depends|Description|Homepage|OriginalMaintainer|Replaces|Breaks|Enhances|Provides|Conflicts|Recommends|Conffiles|Suggests|PreDepends|Essential|BuiltUsing|Origin|Bugs}
     */
    get(propertyString) {
        arg(propertyString, ow.string.nonEmpty);

        let [property, value] = this.splitLimited(propertyString, /:(\s+|\n)/, 2, "\n");
        property = property.trim().rtrim(':');
        value = value.trim();

        if (this.getValidPropertyNameList().indexOf(property) === -1) {
            throw new RuntimeError(`Unexpected property "${property}" found from property string: '${propertyString}'.`);
        }

        const propertyClass = Case.pascal(Case.kebab(property));
        const cls = PropertyClasses[propertyClass];

        return new cls(propertyString, Case.camel(Case.kebab(property.toLowerCase())), value);
    }

}