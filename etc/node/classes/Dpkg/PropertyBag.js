import MagicProperties from '../MagicProperties';
import PropertyHelper from './PropertyHelper';
import Case from 'case';
import PropertyClasses from "./PropertyClasses";
import ArrayIterator from "../ArrayIterator";
import LogicError from "../LogicError";
import ow from 'ow';
import arg from '../Arg';

/**
 * Class PropertyBag
 *
 * A container class that allows iteration over the packages properties.
 *
 * @author Oliver Lillie
 *
 * @property {Package} package
 * @property {Status} status
 * @property {Priority} priority
 * @property {Section} section
 * @property {InstalledSize} installedSize
 * @property {Maintainer} maintainer
 * @property {Architecture} architecture
 * @property {MultiArch} multiArch
 * @property {Source} source
 * @property {Version} version
 * @property {Depends} depends
 * @property {Description} description
 * @property {Homepage} homepage
 * @property {OriginalMaintainer} originalMaintainer
 * @property {Replaces} replaces
 * @property {Breaks} breaks
 * @property {Enhances} enhances
 * @property {Provides} provides
 * @property {Conflicts} conflicts
 * @property {Recommends} recommends
 * @property {Conffiles} conffiles
 * @property {Suggests} suggests
 * @property {PreDepends} preDepends
 * @property {Essential} essential
 * @property {BuiltUsing} builtUsing
 * @property {Origin} origin
 * @property {Bugs} bugs
 */
class PropertyBag extends ArrayIterator {

    /**
     * Handles the dynamic property access.
     *
     * If the property does not exist within the property bag then the property
     * object is created with a null value.
     *
     * @author Oliver Lillie
     *
     * @param {string} property
     *
     * @return {Property}
     */
    __get(property) {
        arg(property, ow.string.nonEmpty);

        if(PropertyHelper.getValidPropertyIdList().indexOf(property) === -1) {
            return null;
        }

        const foundProperty = this._manifest.find((prop) => prop.property === property)
        if(foundProperty) {
            return foundProperty;
        }

        const propertyClass = Case.pascal(Case.camel(property));
        const cls = PropertyClasses[propertyClass];

        let packageName = Case.kebab(Case.camel(property));
        packageName = packageName.charAt(0).toUpperCase() + packageName.substr(1);

        return new cls(`${packageName}: `, property, null);
    }

    /**
     * Determines if the property exists on the property bag.
     *
     * @author Oliver Lillie
     *
     * @param {string} property
     *
     * @return bool
     */
    __isset(property) {
        arg(property, ow.string.nonEmpty);

        return PropertyHelper.getValidPropertyIdList().indexOf(property) >= 0;
    }

    /**
     * Prevents properties from being set.
     *
     * @author Oliver Lillie
     *
     * @param {string} property
     *
     * @return void
     * @throws \LogicException
     */
    __set(property) {
        arg(property, ow.string.nonEmpty);

        throw new LogicError(`Trying to set ${property}, however you cannot set values to PropertyBag.`);
    }

}

module.exports = MagicProperties(PropertyBag);