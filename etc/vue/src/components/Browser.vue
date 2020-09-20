<template>
    <div class="dpkg-package-browser" v-bind:class="{ 'has-package': hasPackage, 'has-loaded-package': hasLoadedPackage }">
        <div ref="scroller" class="scroller">
            <ul>
                <li v-for="(pkg, index) in packages" class="package" ref="pkg-parent">
                    <Package ref="pkg"
                             :packageName="pkg.packageName"
                             :index="index"
                             :previousPackage="pkg.previousPackage"
                             :parentPackage="pkg.parentPackage"
                             :parentBrowser="pkg.parentBrowser"
                             @packageLoaded="handlePackageLoaded"
                             @removePackage="handleRemovePackage"
                             @dependancyClicked="handleDependancyClick"
                             @dependantClicked="handleDependantClicked"
                    ></Package>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
'use strict';

import Toasted from 'vue-toasted';
import Package from './Package';

Vue.use(Toasted);

/**
 * The iterative delay imposed in the "remove" classes being added to the
 * packages when they are being removed. It provides a gradual fall out
 * animation.
 *
 * @author Oliver Lillie
 * @type {number}
 */
const removeTimeoutIteration = 50;

/**
 * The corresponding css delay that is used in the "fall out" css animation when
 * a package is removed from the browser.
 *
 * IMPORTANT: If updated, the corresponding transition animation time should
 * also be updated.
 *
 * @type {number}
 */
const removeTimeoutDelay = 200;

class BrowserPackage {
    constructor(packageName, previousPackage, parentPackage, parentBrowser, parentPackageIndex, connectedPackage, connectedBrowser) {
        this.packageName = packageName;
        this.previousPackage = previousPackage;
        this.parentPackage = parentPackage;
        this.parentBrowser = parentBrowser;
        this.parentPackageIndex = parentPackageIndex;
        this.connectedPackage = connectedPackage;
        this.connectedBrowser = connectedBrowser;
    }
}

/**
 * @module BrowserPackage
 */
export { BrowserPackage };

/**
 * @module Browser
 */
export default {

    inject: [
        /**
         * Inject getList so that the browser can directly access the browser
         * without convoluted $parent calls.
         *
         * @author Oliver Lillie
         */
        'getList',

        /**
         * Inject getPath from App so the api can easily access the base path
         * that the curernt api is using.
         *
         * @author Oliver Lillie
         */
        'getPath'
    ],

    provide() {
        return {
            /**
             * Direct access to the list to each package so there are not
             * $parent.$parent chains in the packages.
             *
             * @author Oliver Lillie
             */
            getList: () => this.getList(),

            /**
             * Direct access to the current parent browser of each package so
             * there are not $parent calls in the packages.
             *
             * @author Oliver Lillie
             */
            getBrowser: () => this,
        }
    },

    data: () => ({
        /**
         * Determines if the browser has a package and that package has had its
         * data loaded from the server and is ready to go.
         *
         * @author Oliver Lillie
         * @type {boolean}
         */
        hasPackage: false,

        /**
         * Determines if the browser contains a package that has had it's data
         * loaded from the server.
         *
         * @author Oliver Lillie
         * @type boolean
         */
        hasLoadedPackage: false
    }),

    props: {
        /**
         * Contains a list of packages in the browser.
         *
         * This property is synced.
         *
         * @author Oliver Lillie
         * @type {BrowserPackage[]}
         */
        packages: Array,
        index:Number
    },

    components: {
        Package
    },

    watch: {
        /**
         * Watches the packages for updates and when packages are found it
         * updates the `hasPackage` boolean flag.
         *
         * @author Oliver Lillie
         * @param {BrowserPackage[]} val
         */
        packages: {
            handler(val) {
                this.hasPackage = val.length > 0;
            }
        }
    },

    methods: {

        /**
         * Handles a dependancy click event that is propagated from a Package
         * component.
         *
         * The packages in the browser from the source packages index + 1 are
         * removed so the new navigation from the clicked dependancy can take
         * place.
         *
         * @see Package.methods.clickDependancy
         * @listens module:Package~dependancyClicked
         * @author Oliver Lillie
         * @param {Event} event
         */
        handleDependancyClick(event) {
            const fromPackageIndex = this.packages.findIndex((pkg) => pkg.packageName === event.fromPackage.pkg.packageName);
            this.removePackagesFromIndex(fromPackageIndex + 1).then(
                () => {
                    this.$nextTick(
                        () => this.packages.push(
                            new BrowserPackage(
                                event.packageName,
                                event.fromPackage,
                                null,
                                null,
                                null,
                                null,
                                null
                            )
                        )
                    );
                }
            );
        },

        /**
         * Propagates up the component stack the event from Package that occurs
         * when a dependant is clicked.
         *
         * @see Package.methods.clickDependant
         * @listens module:Package~dependantClicked
         * @fires module:Browser~dependantClicked
         * @author Oliver Lillie
         * @param {Event} event
         */
        handleDependantClicked(event) {
            /**
             * Event reporting that the browser has had a package that has a
             * dependancy clicked.
             *
             * @author Oliver Lillie
             * @event module:Browser~dependantClicked
             * @property {string} packageName
             * @property {packageName} fromPackage
             * @property {index} index
             */
            this.$emit('dependantClicked', event);
        },

        /**
         * Handles the event propagated from a package when the close button is
         * clicked or where the api directly calls Pacakge.clearPackage.
         *
         * @author Oliver Lillie
         * @see Package.methods.clearPackage
         * @listens module:Package~removePackage
         * @param {Event} event
         */
        handleRemovePackage(event) {
            this.removePackage(event.package);
        },

        /**
         * Handles the event propagated from a Package that has had its data
         * loaded from the server and inserted into the component.
         *
         * This connects any previous package in the browser to the new package,
         * only if there is a previous package.
         *
         * The internal browser scroller width is adjusted so the packages float
         * horizontally at all times.
         *
         * @author Oliver Lillie
         * @see Package.watch.pkg
         * @listens module:Package~packageLoaded
         * @fires module:Browser~packageLoaded
         * @param {Event} event
         */
        handlePackageLoaded(event) {
            // same browser so update the right connector arrows
            if(event.browser === this) {
                if(event.currentPackage.previousPackage) {
                    event.currentPackage.previousPackage.connectRight(event);
                }
            }

            this.adjustScrollerWidth();
            this.hasLoadedPackage = true;

            /**
             * Event reporting that the browser has had a package has been
             * loaded.
             *
             * @author Oliver Lillie
             * @event module:Browser~packageLoaded
             * @event module:Package~packageLoaded
             * @property {Package} currentPackage
             * @property {Browser} browser
             */
            this.$emit('packageLoaded', event);
        },

        /**
         * Adjusts thescrollers width so that the browsers float continiously
         * left.
         *
         * @author Oliver Lillie
         */
        adjustScrollerWidth() {
            // + 23 is padding
            if(this.$refs.scroller) {
                this.$refs.scroller.style.width = ((this.$refs.pkg[0].$el.getBoundingClientRect().width + 23) * this.packages.length) + 'px';
            }
        },

        /**
         * Removes the package requested package and all of the packages to the
         * right of the requested package.
         *
         * @author Oliver Lillie
         * @param {string} packageName
         * @return {*}
         */
        removePackage(packageName) {
            const promise = this.removePackagesFromIndex(
                this.$refs.pkg.findIndex((pkg) => pkg === packageName)
            );

            promise.then(() => this.adjustScrollerWidth());

            return promise;
        },

        /**
         * Removes all of the packages in the browser from the given index.
         *
         * Returns a promise that is resolves when the last package has been
         * removed and destroyed.
         *
         * @author Oliver Lillie
         * @param {int} from_index
         * @return {Promise}
         */
        removePackagesFromIndex(from_index) {
            return new Promise((resolve) => {
                const packages_to_remove = this.packages.slice(from_index);
                const to_destroy = packages_to_remove.length;
                if(to_destroy === 0) {
                    resolve();
                    return;
                }

                // a setTimeout chain to provide a "fade out" animation delay
                // to each package whilst keeping track of all the packages
                // that have been removed. Once the destroyed === total count
                // is reached the promise is resolved.
                let destroyed = 0;
                let timeout = 0;
                packages_to_remove.forEach(
                    (pkg, index) => {
                        setTimeout(
                            (index) => {
                                this.$refs['pkg'][index].remove();
                                setTimeout(
                                    (index) => {
                                        if(this.$refs['pkg'][index]) {
                                            this.$refs['pkg'][index].$destroy();
                                        }
                                        destroyed++;
                                        if(destroyed === to_destroy) {
                                            this.packages.splice(from_index, to_destroy);
                                            if(this.packages.length === 0) {
                                                /**
                                                 * Event reporting that the browser has completely emptied of packages.
                                                 *
                                                 * @author Oliver Lillie
                                                 * @event module:Browser~browserEmptied
                                                 */
                                                this.$emit('browserEmptied', {});
                                            }
                                            resolve();
                                        }
                                    },
                                    removeTimeoutDelay,
                                    index
                                );
                            },
                            timeout,
                            from_index + index
                        );
                        timeout += removeTimeoutIteration;
                    }
                );
            });
        },

        /**
         * Updates the x position of the browser relative to a Packages x
         * position.
         *
         * @author Oliver Lillie
         * @param {Package} relativeToPackage
         */
        positionRelativeToPackage(relativeToPackage) {
            // -15 is padding of the browsercontainer
            this.$el.style.marginLeft = (relativeToPackage.getBrowser().$el.offsetLeft + relativeToPackage.$el.offsetLeft - this.getList().$el.offsetWidth - 15) + 'px';
        },

        /**
         * Updates the position of the browser relative to the package list item
         * in the List so that the first browser is always positioned next to
         * the selected item in the list.
         *
         * @author Oliver Lillie
         * @param {Element} listItemNode
         */
        positionRelativeToListNode(listItemNode) {
            // -50 is the body contains padding from the top
            // -5 is the internal padding of a packge.
            this.$el.style.marginTop = (listItemNode.offsetTop - 50 - 5) + 'px';
        }
    }

};

</script>

<style lang="scss">

.dpkg-package-browser {
    float: left;
    width: 100%;
    position: relative;
    display: none;

    li.package {
        width: 320px;
        float: left;
        margin-left: 12px;
        white-space: normal;
        margin-right: 11px;
    }

    &.has-loaded-package {
        display: block;
    }

    &:not(.top) {
        margin-top: 20px;
    }
}

html.lte-650 {

    .dpkg-package-browser {
        .scroller {
            width:calc(100% - 15px) !important;
        }

        li.package {
            margin-top: 20px;
            width:100%;
        }
    }
}


</style>