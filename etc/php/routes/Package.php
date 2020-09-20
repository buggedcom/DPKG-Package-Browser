<?php

namespace DpkgBrowser\Routes;

use DpkgBrowser\Classes\BaseRoute;
use DpkgBrowser\Classes\Cache;
use DpkgBrowser\Classes\JsonPayload;
use DpkgBrowser\Classes\JsonPayloadError;
use DpkgBrowser\Classes\Tick;

/**
 * Class Package
 *
 * Finds a specific packages information, but limits the returned data to only
 * "package", "description" and "depends". To return more data in the payload
 * add the related properties to the `return_properties` setting in
 * /etc/config.ini.
 *
 * @author Oliver Lillie
 * @package DpkgBrowser\Routes
 */
class Package extends BaseRoute {
    public function get($id): string {
        $collection = $this->dpkg()->filter('package', $id, true);

        $payload_meta = [
            'requested' => $id,
            'actual'    => $id,
        ];

        $provided_to_package_map = $this->dpkg()->getPackageToProvidedMap();

        // a first initial check to see if the package is found
        // if a package is not found, it means that it could be a program
        // that has been provided by another programme and should therefore
        // be returned slightly differently so that the rendering can be
        // different
        $package_count = $collection->count();
        if ($package_count === 0) {
            if (isset($provided_to_package_map[$id]) === true) {
                $collection = $this->dpkg()->filter('package', $provided_to_package_map[$id], true);
                $package_count = $collection->count();

                $payload_meta['actual'] = $provided_to_package_map[$id];
            }
        }

        if ($package_count === 0) {
            (new JsonPayloadError('Package not found.', 'not-found'))->output();
        } else if ($package_count > 1) {
            (new JsonPayloadError('Package is ambiguous.', 'ambigous'))->output();
        }

        // we only want certain things to be returned in the json payload since
        // most of it is not used by the frontend. If in the future you need
        // access to more information from the package add a config value in
        // etc/config.ini
        $package = $collection[0];
        $properties_to_return = $this->config()->return_properties;
        call_user_func_array([$package, 'filterPropertyBag'], $properties_to_return);

        // this is where we process the packages dependant on the requested
        // package – however we only need the package name so we filter the
        // property bags to prevent returning a lot of information and then
        // just map the package names into the returned meta.
        $dependants_collection = $this->dpkg()->getDependantsForPackage($package)->filterPropertyBags('package');
        $dependants_list = array_map(
            function (\DpkgBrowser\Classes\Dpkg\Package $package) {
                return (string)$package->package;
            },
            $dependants_collection->toArray()
        );

        $package_array = $package->encodeForJson();
        $package_array['dependants'] = $dependants_list;

        // now we get the list of available packages so that we can feed back to
        // the ui if any of the packages listed as depends/preDepends are
        // actually viewable or not, since some doen't actually contain
        // references that are in dpkg
        // In addition, this is located here and not buried in the dpkg parser
        // since this is pure UI logic.
        $available_packages = $this->dpkg()->getPackageList();
        foreach (['depends', 'preDepends'] as $property) {
            if (empty($package_array[$property]) === false) {
                $package_array[$property] = array_map(
                    function ($dependant) use ($available_packages, $provided_to_package_map) {
                        if (isset($dependant[0]) === true) {
                            return array_map(
                                function ($dependant) use ($available_packages, $provided_to_package_map) {
                                    $viewable = in_array($dependant['packageName'], $available_packages, true);
                                    if ($viewable === false) {
                                        $viewable = isset($provided_to_package_map[$dependant['packageName']]);
                                    }

                                    return [
                                        'packageName'  => $dependant['packageName'],
                                        'version'      => $dependant['version'],
                                        'viewable'     => $viewable
                                    ];
                                },
                                $dependant
                            );
                        }

                        $viewable = in_array($dependant['packageName'], $available_packages, true);
                        if ($viewable === false) {
                            $viewable = isset($provided_to_package_map[$dependant['packageName']]);
                        }

                        return [
                            'packageName' => $dependant['packageName'],
                            'version'      => $dependant['version'],
                            'viewable'     => $viewable
                        ];
                    },
                    $package_array[$property]
                );
            }
        }

        // now we find out if the dependances actually exist within the dpkg
        // status file, because if they do not they are not navigatable and
        // therefore should be displayed differently.

        (new JsonPayload($package_array, $payload_meta))->output();
    }
}
