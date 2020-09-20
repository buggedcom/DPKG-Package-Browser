import express from 'express';
import JsonPayload from '../classes/JsonPayload';
import JsonPayloadError from '../classes/JsonPayloadError';
import Dpkg from '../classes/Dpkg';
import Config from '../classes/Config';

const router = express.Router();

/**
 * Lists out all the package names alphabetically.
 *
 * @author Oliver Lillie
 */
router.get(
    '/',
    function(request, response, next) {
        let data = {};

        const dpkg = Dpkg.instance();
        dpkg.forEach((pkg) => {
            data[pkg.package.value] = pkg.description.summary;
        });

        let sorted = {};
        const summaries = Object.values(data);
        Object.keys(data)
            .sort()
            .forEach((packageName, index) => sorted[packageName] = summaries[index]);

        response.json(
            (new JsonPayload(sorted)).toObject()
        );
    }
);


/**
 * Finds a specific packages information, but limits the returned data to only
 * "package", "description" and "depends". To return more data in the payload
 * add the related properties to the `return_properties` setting in
 * /etc/config.ini.
 *
 * @author Oliver Lillie
 */
router.get(
    '/:id',
    function(request, response, next) {
        const id = request.params.id;

        const dpkg = Dpkg.instance();
        let collection = dpkg.filter('package', id, true);

        let payloadMeta = {
            requested: id,
            actual: id,
            time: new Date().toISOString()
        };

        let providedToPackageMap = dpkg.getPackageToProvidedMap();

        if(collection.length === 0) {
            if(typeof providedToPackageMap[id] !== 'undefined') {
                collection = dpkg.filter('package', providedToPackageMap[id], true);
                payloadMeta['actual'] = providedToPackageMap[id];
            }
        }

        if(collection.length === 0) {
            response.json((new JsonPayloadError('Package not found.', 'not-found')).toObject());
            return;
        } else if(collection.length > 1) {
            response.json((new JsonPayloadError('Package is ambiguous.', 'ambigous')).toObject());
            return;
        }

        const pkg = collection.offsetGet(0);
        const propertiesToReturn = Config.instance().return_properties;
        pkg.filterPropertyBag(propertiesToReturn);

        let packageArray = pkg.encodeForJson();

        const dependantsCollection = dpkg.getDependantsForPackage(pkg).filterPropertyBags(['package']);
        packageArray['dependants'] = dependantsCollection.map((pkg) => pkg.package.value);

        let availablePackages = dpkg.getPackageList();
        ['depends', 'preDepends'].forEach(
            (property) => {
                if(property in packageArray) {
                    packageArray[property] = packageArray[property].map(
                        (dependant) => {
                            if(!!dependant[0]) {
                                return dependant.map(
                                    (dependant) => {
                                        let viewable = availablePackages.indexOf(dependant.packageName) >= 0;
                                        if(viewable === false) {
                                            viewable = typeof providedToPackageMap[dependant.packageName] !== 'undefined';
                                        }

                                        return {
                                            packageName: dependant.packageName,
                                            version: dependant.version,
                                            viewable: viewable
                                        };
                                    }
                                );
                            }

                            let viewable = availablePackages.indexOf(dependant.packageName) >= 0;
                            if(viewable === false) {
                                viewable = typeof providedToPackageMap[dependant.packageName] !== 'undefined';
                            }

                            return {
                                packageName: dependant.packageName,
                                version: dependant.version,
                                viewable: viewable
                            };
                        }
                    );
                }
            }
        );

        response.json(
            (new JsonPayload(packageArray, payloadMeta)).toObject()
        );
    }
);


module.exports = router;
