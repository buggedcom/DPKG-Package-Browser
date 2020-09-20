<template>
    <div
         :aria-labelledby="pkg.packageName + '_header'"
         :aria-describedby="pkg.packageName + '_description'"
         @keydown.tab.shift="handlePackageArrowLeft"
         @keydown.up="handlePackageArrowUp"
         @keydown.down="handlePackageArrowDown"
         @keydown.right="handlePackageArrowRight"
         @keydown.left="handlePackageArrowLeft"
         @keydown.enter="handlePackageEnter"
         @keydown.esc="handlePackageEscape"
         :tabindex="tabIndex('package', 'box')"
         class="dpkg-package"
         v-bind:class="{ 'has-package': hasPackage }"
         :style="getBrowserStyle()"
    >
        <div ref="packageInner" class="package">
            <div class="package-details-container">

                <h3 :id="pkg.packageName + '_header'" class="header" v-if="pkg.packageName === pkg.requested">{{ pkg.packageName }}</h3>
                <h3 :id="pkg.packageName + '_header'" class="header" v-else>{{ pkg.requested }} <span class="actual">via {{ pkg.packageName }}</span></h3>

                <div :id="pkg.packageName + '_description'" class="description" role="definition"><VueMarkdown :source="pkg.description.verbose" :emoji="false" :html="false" :anchorAttributes="{target:'_blank'}"></VueMarkdown></div>

                <button aria-label="Close package tree" :tabindex="tabIndex('package', 'close')" class="icon-close" @click="clearPackage"></button>

                <!-- Note, preDepends and depends are essentially the same thing so are treated as such -->

                <div role="group" :aria-labelledby="pkg.packageName + '_requires_subheader'" v-if="pkg.depends.length > 0 || pkg.preDepends.length > 0" class="dependancies-group">
                    <div :id="pkg.packageName + '_requires_subheader'" class="sub-header">Requires</div>
                    <ul>
                        <li role="listitem" v-for="(uses) in pkg.depends" :class="{ alternatives : uses instanceof Array }">
                            <button
                                role="button"
                                v-if="uses instanceof Array === false"
                                :aria-label="uses.packageName"
                                :aria-disabled="!uses.viewable"
                                :tabindex="tabIndex('package', 'dependancy')"
                                :data-packagename="uses.packageName"
                                :data-index="uses.index"
                                @click="clickDependancy(uses, 'depends', uses.index)"
                                @keydown.up="handleDependancyArrowLeft"
                                @keydown.down="handleDependancyArrowRight"
                                @keydown.left="handleDependancyArrowLeft"
                                @keydown.right="handleDependancyArrowRight"
                                @keydown.esc="handleDependancyEscape"
                                :ref="uses instanceof Array ? '' : 'depends'"
                                class="depends"
                                :class="{'viewable': uses.viewable}"
                                :style="'z-index: ' + (999999 - uses.index) + ';'"
                            >
                                <span class="button-body">
                                    {{ uses.packageName }}
                                    <span aria-hidden="true" class="icon icon-view-show"></span>
                                </span>
                            </button>
                            <span v-else>
                                <ul class="alternatives">
                                    <li role="listitem" v-for="(alt) in uses">
                                        <button
                                            role="button"
                                            :aria-label="alt.packageName"
                                            :aria-disabled="!alt.viewable"
                                            :tabindex="tabIndex('package', 'dependancy')"
                                            :data-packagename="alt.packageName"
                                            :data-index="alt.index"
                                            @click="clickDependancy(alt, 'depends', alt.index)"
                                            @keydown.up="handleDependancyArrowLeft"
                                            @keydown.down="handleDependancyArrowRight"
                                            @keydown.left="handleDependancyArrowLeft"
                                            @keydown.right="handleDependancyArrowRight"
                                            @keydown.esc="handleDependancyEscape"
                                            ref="depends"
                                            class="depends alt"
                                            :class="{'viewable': alt.viewable}"
                                            :style="'z-index: ' + (999999 - alt.index) + ';'"
                                        >
                                            <span class="button-body">
                                                {{ alt.packageName }}
                                                <span aria-hidden="true" class="icon icon-view-show"></span>
                                            </span>
                                        </button>
                                    </li>
                                </ul>
                            </span>
                        </li>

                        <li role="listitem" v-for="(uses) in pkg.preDepends" :class="{ alternatives : uses instanceof Array }">
                            <button
                                role="button"
                                v-if="uses instanceof Array === false"
                                :aria-label="uses.packageName"
                                :aria-disabled="!uses.viewable"
                                :tabindex="tabIndex('package', 'dependancy')"
                                :data-packagename="uses.packageName"
                                :data-index="uses.index"
                                @click="clickDependancy(uses, 'preDepends', uses.index)"
                                @keydown.up="handleDependancyArrowLeft"
                                @keydown.down="handleDependancyArrowRight"
                                @keydown.left="handleDependancyArrowLeft"
                                @keydown.right="handleDependancyArrowRight"
                                @keydown.esc="handleDependancyEscape"
                                :ref="uses instanceof Array ? '' : 'preDepends'"
                                class="depends"
                                :class="{'viewable': uses.viewable}"
                                :style="'z-index: ' + (999999 - uses.index) + ';'"
                            >
                                <span class="button-body">
                                    {{ uses.packageName }}
                                    <span aria-hidden="true" class="icon icon-view-show"></span>
                                </span>
                            </button>
                            <span v-else>
                                <ul class="alternatives">
                                    <li role="listitem" v-for="(alt) in uses">
                                        <button
                                            role="button"
                                            :aria-label="alt.packageName"
                                            :aria-disabled="!alt.viewable" 
                                            :tabindex="tabIndex('package', 'dependancy')"
                                            :data-packagename="alt.packageName"
                                            :data-index="alt.index"
                                            @click="clickDependancy(alt, 'preDepends', alt.index)"
                                            @keydown.up="handleDependancyArrowLeft"
                                            @keydown.down="handleDependancyArrowRight"
                                            @keydown.left="handleDependancyArrowLeft"
                                            @keydown.right="handleDependancyArrowRight"
                                            @keydown.esc="handleDependancyEscape"
                                            ref="preDepends"
                                            class="depends alt"
                                            :class="{'viewable': alt.viewable}"
                                            :style="'z-index: ' + (999999 - alt.index) + ';'"
                                        >
                                            <span class="button-body">
                                                {{ alt.packageName }}
                                                <span aria-hidden="true" class="icon icon-view-show"></span>
                                            </span>
                                        </button>
                                    </li>
                                </ul>
                            </span>
                        </li>
                    </ul>
                </div>

                <div role="group" :aria-labelledby="pkg.packageName + '_used_by_subheader'" v-if="pkg.dependants.length > 0" class="dependancies-group">
                    <div :id="pkg.packageName + '_used_by_subheader'" class="sub-header">Used by</div>
                    <ul>
                        <li role="listitem" v-for="(dependant, index) in pkg.dependants">
                            <button
                                role="button"
                                :aria-label="dependant"
                                :tabindex="tabIndex('package', 'dependancy')"
                                :data-packagename="dependant"
                                @click="clickDependant(dependant, index)"
                                @keydown.up="handleDependancyArrowLeft"
                                @keydown.down="handleDependancyArrowRight"
                                @keydown.left="handleDependancyArrowLeft"
                                @keydown.right="handleDependancyArrowRight"
                                @keydown.esc="handleDependancyEscape"
                                ref="dependancies"
                                class="dependancy viewable"
                            >
                                <span class="button-body">
                                    {{ dependant }}
                                    <span aria-hidden="true" class="icon icon-view-show"></span>
                                </span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <div ref="connectorsContainer" class="connectors-container">
                <Connector ref="connectorRight" v-if="connectsRight" direction="right" :origin="connectsRight" :target="nextPackage && nextPackage.$el"></Connector>
                <Connector ref="connectorDown" v-if="connectsDown" direction="down" :origin="connectsDown" :target="connectedPackage && connectedPackage.$el"></Connector>
            </div> 

        </div>
        <div ref="shadow" class="focus-shadow"></div>
    </div>
</template>

<script>
'use strict';

import Toasted from 'vue-toasted';
import { path } from '../helpers/Helpers';
import VueMarkdown from 'vue-markdown';
import Connector from './Connector.vue';
import { tabIndex } from '../helpers/KeyboardInteraction';

Vue.use(Toasted);

class Package {
    constructor(packageName, description, depends, preDepends, dependants, requested) {
        this.packageName = packageName;
        this.description = description || {summary:'', verbose:''};
        this.depends = depends || [];
        this.preDepends = preDepends || [];
        this.dependants = dependants || [];
        this.requested = requested;

        this._indexDependancyGroup('depends');
        this._indexDependancyGroup('preDepends');
    }

    /**
     * Because of alterntive groupings within the various depends/preDepends
     * we have to supply an arificial index value to each package in the arrays.
     * This loops through everything attaching that index.
     *
     * @author Oliver Lillie
     * @param {string} group The name of the group to process.
     * @private
     */
    _indexDependancyGroup(group) {
        let counter = 0;
        this[group].forEach(
            (depend, index) => {
                if(depend instanceof Array === true) {
                    this[group][index].forEach(
                        (alt, alt_index) => {
                            this[group][index][alt_index].index = counter;
                            counter++;
                        }
                    );
                } else {
                    this[group][index].index = counter;
                    counter++;
                }
            }
        );
    }

}

/**
 * @module Package
 */
export default {

    inject: [
        /**
         * Inject getList so that the browser can directly access the list of
         * packages without convoluted $parent calls.
         *
         * @author Oliver Lillie
         */
        'getList',

        /**
         * Inject getBrowser so that the browser can directly access the browser
         * without convoluted $parent calls.
         *
         * @author Oliver Lillie
         */
        'getBrowser',

        /**
         * Inject getPath from App so the api can easily access the base path
         * that the curernt api is using.
         *
         * @author Oliver Lillie
         */
        'getPath'
    ],

    components: {
        VueMarkdown,
        Connector
    },

    data: function() {
        return {
            /**
             * This is the current top position of the package. It is watched
             * and so that the connected arrows can be reposition if any of the
             * related packages are added/removed.
             *
             * The related interval for this is defined via topWatchInterval.
             *
             * @author Oliver Lillie
             * @see Package.mounted
             * @type {int|null}
             */
            top: null,

            /**
             * Related to the property `data`. This contains the interval that
             * is continuously checking the position of the package.
             *
             * @author Oliver Lillie
             * @see Package.mounted
             * @type {number|null}
             */
            topWatchInterval: null,

            /**
             * This contains any ongoing xhr requests to the server for fetching
             * package information. It is retained in data so that if a package
             * is destroyed the XHR can be aborted.
             *
             * @author Oliver Lillie
             * @type {AbortController|null}
             */
            xhrController: null,

            /**
             * A boolean toggle that determines if the package data has been
             * loaded into the package yet.
             *
             * @author Oliver Lillie
             * @type {boolean}
             */
            hasPackage: false,

            /**
             * The container for the current package data. It is by default an
             * empty instance so that there are no js errors when initially
             * rendering the template.
             *
             * @author Oliver Lillie
             * @type Package
             */
            pkg: new Package(),

            /**
             * This contains a reference to dependant browser that has been
             * opened by clicking on a link from a depenant package.
             *
             * @author Oliver Lillie
             * @type {Browser|null}
             */
            connectedBrowser: null,

            /**
             * This contains a reference to dependant package that has been
             * opened by clicking on a link from a depenant package.
             *
             * @author Oliver Lillie
             * @type {Package|null}
             */
            connectedPackage: null,

            /**
             * A short cut for referencing the downarrow dependant node.
             *
             * @author Oliver Lillie
             * @type {Element|null}
             */
            downArrowDependancyNode: null,

            /**
             * This contains a reference to the next package in the browser
             * chain.
             *
             * @author Oliver Lillie
             * @type {Package|null}
             */
            nextPackage: null,

            /**
             * A boolean toggle for determining if the package is connecting
             * rightwards to another package in the same browser.
             *
             * @author Oliver Lillie
             * @type boolean
             */
            connectsRight: false,

            /**
             * A boolean toggle for determining if the package is connecting
             * downwards to another package in the same browser.
             *
             * @author Oliver Lillie
             * @type boolean
             */
            connectsDown: false
        };
    },

    props: [
        'packageName',
        'index',
        'previousPackage',
        'parentPackage',
        'parentBrowser'
    ],

    watch: {

        /**
         * Monitors the top position value of the package so that any connected
         * downwards packages can have the arrow repointed to them if the
         * positions of packages have been updated.
         *
         * @author Oliver Lillie
         * @param {float} value
         * @param {float} old_value
         */
        top(value, old_value) {
            if(old_value !== null) {
                if(this.parentPackage && this.parentPackage.$refs.connectorDown) {
                    this.parentPackage.$refs.connectorDown.layout();
                }
            }
        },

        /**
         * Monitors the pkg value so when it updates and the package data has
         * been loaded we can propagate that event up the stack.
         *
         * @author Oliver Lillie
         * @fires module:Package~packageLoaded
         */
        pkg() {
            /**
             * Event reporting that the package data has been loaded from the
             * server and inserted into the component.
             *
             * @author Oliver Lillie
             * @event module:Package~packageLoaded
             * @property {Package} currentPackage
             * @property {Browser} browser
             */
            this.$emit(
                'packageLoaded',
                {
                    currentPackage: this,
                    browser: this.getBrowser()
                }
            );
        }
    },

    methods: {

        /**
         * @see KeyboardInteraction.tabIndex
         */
        tabIndex: tabIndex,

        /**
         * Focuses the package's root node.
         *
         * @author Oliver Lillie
         */
        focus() {
            this.$el.focus({
                preventScroll: true
            });
        },

        /**
         * Checks if the events target is the packages root element or not.
         *
         * @author Oliver Lillie
         * @param {KeyboardEvent} event
         * @type boolean
         */
        _checkEventTargetIsRootElement(event) {
            return event.target === this.$el;
        },

        /**
         * Handles the keyboard event when the up arrow is pressed on a focused
         * package root node.
         *
         * If a parentPackage prop is found then that package is then focused to
         * move the focus "up".
         *
         * @author Oliver Lillie
         * @param {KeyboardEvent} event
         */
        handlePackageArrowUp(event) {
            if(this._checkEventTargetIsRootElement(event) === true && this.parentPackage) {
                this.parentPackage.focus();
                event.preventDefault();
            }
        },

        /**
         * Handles the keyboard event when the down arrow is pressed on a
         * focused package root node.
         *
         * If a connectedPackage data value is found then that package is then
         * focused to move the focus "down".
         *
         * @author Oliver Lillie
         * @param {KeyboardEvent} event
         */
        handlePackageArrowDown(event) {
            if(this._checkEventTargetIsRootElement(event) === true && this.connectedPackage) {
                this.connectedPackage.focus();
                event.preventDefault();
            }
        },

        /**
         * Handles the keyboard event when the right arrow or when tab is
         * pressed on a focused package root node.
         *
         * If a nextPackage prop value is found then that package is then
         * focused to move the focus "right".
         *
         * @author Oliver Lillie
         * @param {KeyboardEvent} event
         */
        handlePackageArrowRight(event) {
            if(this._checkEventTargetIsRootElement(event) === true && this.nextPackage) {
                this.nextPackage.focus();
                event.preventDefault();
            }
        },

        /**
         * Handles the keyboard event when the left arrow or when shift+tab
         * is pressed on a focused package root node.
         *
         * If a previousPackage prop value is found then that package is then
         * focused to move the focus "right".
         *
         * If there is no previousPackage and the packages browser is the top
         * browser then the focus is moved onto the List's selected list item.
         *
         * @author Oliver Lillie
         * @param {KeyboardEvent} event
         */
        handlePackageArrowLeft(event) {
            if(this._checkEventTargetIsRootElement(event) === true) {
                if(this.previousPackage) {
                    this.previousPackage.focus();
                    event.preventDefault();
                } else if(this.parentPackage) {
                    this.parentPackage.focus();
                    event.preventDefault();
                } else if(this.index === 0 && this.getBrowser().index === 0) {
                    this.getList().focusSelected();
                    event.preventDefault();
                }
            }
        },

        /**
         * Handles the keyboard event when the enter key is pressed on a focused
         * package root node.
         *
         * The focus is moved from the outer root node of the package to the
         * internal dependancy button nodes to allow tab/arrow focus traversal.
         *
         * @author Oliver Lillie
         * @param {KeyboardEvent} event
         */
        handlePackageEnter(event) {
            if(this._checkEventTargetIsRootElement(event) === true) {
                if(this.$refs.depends && this.$refs.depends.length) {
                    this.$refs.depends[0].focus();
                    event.preventDefault();
                } else if(this.$refs.preDepends && this.$refs.preDepends.length) {
                    this.$refs.preDepends[0].focus();
                    event.preventDefault();
                } else if(this.$refs.dependancies && this.$refs.dependancies.length) {
                    this.$refs.dependancies[0].focus();
                    event.preventDefault();
                }
            }
        },

        /**
         * Handles the keyboard event when the esc key is pressed on a focused
         * package root node.
         *
         * The package is closed if the escape key is pressed.
         *
         * @author Oliver Lillie
         * @param {KeyboardEvent} event
         */
        handlePackageEscape(event) {
            if(this._checkEventTargetIsRootElement(event) === true) {
                event.preventDefault();
                this.clearPackage();
            }
        },

        /**
         * Determines if the event's target is a "depends" dependancy.
         *
         * @author Oliver Lillie
         * @param {KeyboardEvent} event
         * @return {boolean}
         */
        _isEventTargetDepends(event) {
            return event.target.classList.contains('depends') === true;
        },

        /**
         * Determines if the event's target is a "dependancy" dependancy.
         *
         * @author Oliver Lillie
         * @param {KeyboardEvent} event
         * @return {boolean}
         */
        _isEventTargetDependancy(event) {
            return event.target.classList.contains('dependancy') === true;
        },

        /**
         * Determines if the event's target is a "depends" or "dependancy"
         * dependancy.
         *
         * @author Oliver Lillie
         * @param {KeyboardEvent} event
         * @return {boolean}
         */
        _checkEventTargetIsDependancyButton(event) {
            return this._isEventTargetDepends(event)
                || this._isEventTargetDependancy(event)
        },

        /**
         * Handles the keyboard event when the arrow left/up or shift+tab is
         * pressed on a focused package dependancy/depends node.
         *
         * If there are no more dependancy/depends buttons to focus on then the
         * packages root node is given focus. Otherwise the focus is moved to
         * the previous dependancy/depends button.
         *
         * @author Oliver Lillie
         * @param {KeyboardEvent} event
         */
        handleDependancyArrowLeft(event) {
            if(this._checkEventTargetIsDependancyButton(event) === true) {
                if(event.target.parentNode.previousSibling && event.target.parentNode.previousSibling.nodeType === Node.ELEMENT_NODE) {
                    event.target.parentNode.previousSibling.firstChild.focus();
                    event.preventDefault();
                } else if(this._isEventTargetDepends(event) === true) {
                    this.focus();
                    event.preventDefault();
                } else if(this._isEventTargetDependancy(event) === true) {
                    if(this.$refs.depends) {
                        this.$refs.depends[this.$refs.depends.length - 1].focus();
                        event.preventDefault();
                    } else {
                        this.focus();
                        event.preventDefault();
                    }
                }
            }
        },

        /**
         * Handles the keyboard event when the arrow right/down or tab is
         * pressed on a focused package dependancy/depends node.
         *
         * If there are no more dependancy/depends buttons to focus AND there is
         * not nextPackage prop then nothing is focused. If there is a
         * nextPackage prop then the next package is focused.
         *
         * Otherwise the depends/dependacy button nodes will be traversed right
         * and down.
         *
         * @author Oliver Lillie
         * @param {KeyboardEvent} event
         */
        handleDependancyArrowRight(event) {
            if(this._checkEventTargetIsDependancyButton(event) === true) {
                if(event.target.parentNode.nextSibling && event.target.parentNode.nextSibling.nodeType === Node.ELEMENT_NODE) {
                    event.target.parentNode.nextSibling.firstChild.focus();
                    event.preventDefault();
                } else if(this._isEventTargetDepends(event) === true) {
                    if(this.$refs.dependancies) {
                        this.$refs.dependancies[0].focus();
                        event.preventDefault();
                    }
                } else if(this.nextPackage) {
                    this.nextPackage.focus();
                }
            }
        },

        /**
         * Handles the keyboard event when the esc button is pressed on a
         * focused package dependancy/depends node.
         *
         * If a connectedPackage or nextPackage prop is found then the related
         * package is closed.
         *
         * If nothing is connected the package as a whole element isgiven focus
         * to allow the user to break out of the inner package.
         *
         * @author Oliver Lillie
         * @param {KeyboardEvent} event
         */
        handleDependancyEscape(event) {
            if(this._checkEventTargetIsDependancyButton(event) === true) {
                if(this.nextPackage) {
                    event.preventDefault();
                    this.nextPackage.clearPackage();
                } else if(this.connectedPackage) {
                    event.preventDefault();
                    this.connectedPackage.clearPackage();
                } else {
                    event.preventDefault();
                    this.focus();
                }
            }
        },

        /**
         * Removes the package from the browser and disconnects any other
         * connected packages.
         *
         * @author Oliver Lillie
         * @fires module:Package~removePackage
         */
        clearPackage() {
            /**
             * Event reporting that the package has been cleared/removed.
             *
             * @author Oliver Lillie
             * @event module:Package~removePackage
             * @property {Package} package
             */
            this.$emit('removePackage', {
                package: this
            });

            if(this.previousPackage) {
                this.previousPackage.removeRightConnection();
            }

            if(this.parentPackage) {
                this.parentPackage.removeDownConnection();
            }
        },

        /**
         * Cancels any current xhr taking place and requests, loads and process
         * the package data from the server.
         *
         * This triggers reactive changes from updating both data.hasPackage
         * and data.pkg.
         *
         * @author Oliver Lillie
         */
        load() {
            this.cancelCurrentXhr();

            this.getPackageJson().then(
                (json) => {
                    if(!json) {
                        this.clearPackage();
                        return;
                    }

                    this.pkg = new Package(json.data.package, json.data.description, json.data.depends, json.data.preDepends, json.data.dependants, json.meta.requested);
                    this.$nextTick(() => {
                        this.hasPackage = true;
                        this.focus()
                    });
                }
            );
        },

        /**
         * Loads the package data from the server and returns the related
         * XHR promise.
         *
         * If there is an error a toast is shown.
         *
         * @author Oliver Lillie
         * @return {Promise}
         */
        getPackageJson() {
            this.xhrController = new AbortController();
            return fetch(
                this.getPath() + '/packages/' + this.packageName,
                {
                    method: 'get',
                    signal: this.xhrController.signal
                }
            )
            .then((response) => response.json())
            .then((json) => {
                return new Promise(
                    (resolve, reject) => {
                        if(json.status === false) {
                            Vue.toasted.show(
                                json.error.message, {
                                    theme: "toasted-error",
                                    position: "bottom-center",
                                    duration: 5000
                                }
                            );
                            reject(json);
                            return;
                        }

                        resolve(json);
                    }
                );
            })
            .catch((error) => {
                // prevent cancelled xhr's from triggering console.error's
            });
        },

        /**
         * This cancels any active xhr.
         *
         * @author Oliver Lillie
         */
        cancelCurrentXhr() {
            if(!this.xhrController) {
                return;
            }

            this.xhrController.abort();
        },

        /**
         * Connects two packages in the same Browser component by activating the
         * related depenacy and then setting a rightward pointing arrow.
         *
         * @author Oliver Lillie
         * @param {Event} event
         */
        connectRight(event) {
            const packageName = event.currentPackage.packageName;

            let node = null;
            if(this.$refs.depends) {
                node = this.$refs.depends.find((node) => node.dataset.packagename === packageName);
            }
            if(!node) {
                if(this.$refs.preDepends) {
                    node = this.$refs.preDepends.find((node) => node.dataset.packagename === packageName);
                }
            }
            if(node) {
                this.$nextTick(() => {
                    this.connectsRight = node.parentNode; // requires the parent node for position
                    this.nextPackage = event.currentPackage;
                });

                node.classList.remove('loading');
                node.removeAttribute('aria-busy');
                node.classList.add('selected');
            }
        },

        /**
         * Removes the right connection and deselects any active dependancy.
         *
         * @author Oliver Lillie
         */
        removeRightConnection() {
            this.connectsRight = null;
            this.deselectDepends();
        },

        /**
         * Connects two packages in the different Browser components by
         * activating the related depenant and then setting a downward pointing
         * arrow.
         *
         * @author Oliver Lillie
         * @param {Event} event
         */
        connectDown(event) {
            let node = null;
            if(this.$refs.dependancies) {
                const packageName = event.currentPackage.packageName;
                node = this.$refs.dependancies.find((node) => node.dataset.packagename === packageName);
            }

            this.connectedPackage = event.currentPackage;
            this.connectedBrowser = event.currentPackage.$parent;
            this.connectedBrowser.positionRelativeToPackage(this);

            if(node) {
                this.connectsDown = node.parentNode; // requires the parent node for position
                this.downArrowDependancyNode = node.parentNode;

                node.classList.remove('loading');
                node.removeAttribute('aria-busy');
                node.classList.add('selected');
            }
        },

        /**
         * Removes the down connection and deselects any active dependancy.
         *
         * @author Oliver Lillie
         */
        removeDownConnection() {
            this.connectsDown = null;
            this.deselectDependants();
        },

        /**
         * Deselects any active/loading depends or preDepends dependancies.
         *
         * @author Oliver Lillie
         */
        deselectDepends() {
            if(this.$refs.depends) {
                this.$refs.depends.forEach((node) => {
                    node.classList.remove('selected', 'loading');
                    node.removeAttribute('aria-busy');
                });
            }
            if(this.$refs.preDepends) {
                this.$refs.preDepends.forEach((node) => {
                    node.classList.remove('selected', 'loading');
                    node.removeAttribute('aria-busy');
                });
            }
        },

        /**
         * Deselects any dependants
         *
         * @author Oliver Lillie
         */
        deselectDependants() {
            if(this.$refs.dependancies) {
                this.$refs.dependancies.forEach((node) => {
                    node.classList.remove('selected', 'loading');
                    node.removeAttribute('aria-busy');
                });
            }
        },

        /**
         * Handles the click of a dependancy package.
         *
         * If the package is already selected, the related package is closed.
         *
         * If the package is not selected, then an event isbubbled up to the
         * parent browser for the clicked package to be added into the browser.
         *
         * @author Oliver Lillie
         * @param {Object} pkg
         * @param {string} type The type of the dependancy
         * @param {int} index The index within the refs array.
         * @fires module:Package~dependancyClicked
         */
        clickDependancy(pkg, type, index) {
            if(pkg.viewable === false) {
                return;
            }

            const node = this.$refs[type][index];
            const nodeIsSelected = node.classList.contains('selected');

            this.removeRightConnection();

            if(nodeIsSelected === true) {
                this.nextPackage.clearPackage();
                return;
            }

            // done on next tick because the current tick will remove the
            // loading class from the classlist as it calculates classes on
            // render, but since loading is temporary it's not a problem.
            this.$nextTick(() => {
                node.classList.add('loading');
                node.setAttribute('aria-busy', true);
            });

            /**
             * Event reporting that the package has had a dependancy package
             * clicked.
             *
             * @author Oliver Lillie
             * @event module:Package~dependancyClicked
             * @property {string} packageName
             * @property {packageName} fromPackage
             * @property {string} type
             * @property {index} index
             */
            this.$emit('dependancyClicked', {
                packageName: pkg.packageName,
                fromPackage: this,
                type: type,
                index: index
            });
        },

        /**
         * Handles the click of a dependant package.
         *
         * If the package is already selected, the related package is closed.
         *
         * If the package is not selected, then an event isbubbled up to the
         * parent app component for the clicked package to be added into a
         * new browser positioned beneath the current package.
         *
         * @author Oliver Lillie
         * @param {string} packageName
         * @param {int} index The index within the refs array.
         * @fires module:Package~dependancyClicked
         */
        clickDependant(packageName, index) {
            const node = this.$refs.dependancies[index];
            const nodeIsSelected = node.classList.contains('selected');

            this.removeDownConnection();

            if(nodeIsSelected === true) {
                this.connectedPackage.clearPackage();
                return;
            }

            // done on next tick because the current tick will remove the
            // loading class from the classlist as it calculates classes on
            // render, but since loading is temporary it's not a problem.
            this.$nextTick(() => {
                node.classList.add('loading');
                node.setAttribute('aria-busy', 'true');
            });

            /**
             * Event reporting that the package has had a dependant package
             * clicked.
             *
             * @author Oliver Lillie
             * @event module:Package~dependantClicked
             * @property {string} packageName
             * @property {packageName} fromPackage
             * @property {index} index
             */
            this.$emit('dependantClicked', {
                packageName: packageName,
                fromPackage: this,
                index: index
            });
        },

        /**
         * Removes the package and disconnecs the arrows, then cascades the
         * removal down through any connected packages so all connections are
         * also removed when the package is removed.
         *
         * @author Oliver Lillie
         */
        remove() {
            this.removeDownConnection();
            this.removeRightConnection();

            this.$el.classList.add('removing');

            if(this.connectedPackage) {
                this.connectedPackage.clearPackage();
            }
        }
    },

    computed: {
        /**
         * Returns the computed z-index style so that each package has a
         * descending z-index so that the connectors correctly overlap each
         * package.
         *
         * @author Oliver Lillie
         */
        getBrowserStyle() {
            return () => {
                return 'z-index:' + (99999 - this.index) + ';';
            }
        }
    },

    /**
     * Handles the initial load of the packages data and sets up the top value
     * that is continually updated so that arrows can be repositioned when
     * required.
     *
     * @author Oliver Lillie
     */
    mounted() {
        this.load();
        this.topWatchInterval = setInterval(() => this.top = this.$el.getBoundingClientRect().top, 50);
    },

    /**
     * Bit of tidy up, cancels any xhr currenty running and clears the
     * topWatchInterval to prevent memory leaks.
     *
     * @author Oliver Lillie
     */
    beforeDestroy() {
        this.cancelCurrentXhr();
        if(this.topWatchInterval) {
            clearInterval(this.topWatchInterval);
        }
    },

    /**
     * Finally when it is destroyed we must force remove the package element
     * in some situtations.
     *
     * @author Oliver Lillie
     */
    destroyed() {
        if(this.$el.parentNode) {
            this.$el.parentNode.removeChild(this.$el);
        }
    }
};

</script>

<style lang="scss">

    @keyframes pulsePackageDepancyLoading {
        0% {
            transform: scale(.9);
        }
        70% {
            transform: scale(1);
            box-shadow: 0 0 0 10px rgba(255, 243, 0, .5);
        }
        100% {
            transform: scale(.9);
            box-shadow: 0 0 0 0 rgba(255, 243, 0, 0);
        }
    }
    @keyframes pulsePackageDependsLoading {
        0% {
            transform: scale(.9);
        }
        70% {
            transform: scale(1);
            box-shadow: 0 0 0 10px rgba(3, 228, 232, .5);
        }
        100% {
            transform: scale(.9);
            box-shadow: 0 0 0 0 rgba(3, 228, 232, 0);
        }
    }

    .dpkg-package {
        position: relative;
        opacity: 0;
        top: -15px;
        transition: all 0.2s, top 0ms, left 0ms, padding 0ms, width 0ms;

        .package {
            margin-left: 0;
            padding: 8px;
            border-radius: 3px;
            min-width: 320px;
            width: 320px;
            min-height: 32px;
            position: relative;
            transition: all .1s;
            background: rgb(255, 255, 255);
            box-shadow: 0 0 0 1px rgba(0,0,0,.15), 0 2px 3px rgba(0,0,0,.2);
        }

        .header {
            background: rgb(105, 0, 196);
            color: rgb(255, 255, 255);
            border: 1px solid rgb(90, 7, 162);
            border-bottom: 1px solid rgb(94, 2, 158);
            text-shadow: 1px 1px 1px rgb(81, 9, 144);
            padding: 8px;
            margin:-9px -9px 8px -9px;
            border-top-left-radius: 3px;
            border-top-right-radius: 3px;
            font-weight: 500;
            z-index: 3;
            position: relative;

            .actual {
                opacity: 0.4;
            }
        }

        .sub-header {
            font-weight: 700;
            font-size: 0.8em;
            margin-bottom: 10px;
            z-index: 3;
            position: relative;
        }

        .icon-close {
            color: rgb(166, 110, 221);
            transition: all 0.2s;
            position: absolute;
            right: 8px;
            top: 8px;
            cursor: pointer;
            z-index: 5;

            &:hover {
                color: rgb(255, 255, 255);
                transform: scale(1.2);
            }

            &:focus {
                outline: none;
                color: rgba(255, 255, 255, 1);
                background: rgb(137, 0, 255);
                border-radius: 40px;
            }
        }

        .description {
            margin-top: 8px;
            word-break: break-word;
            z-index: 3;
            position: relative;

            p {
                margin-bottom: 8px;
                line-height: 1.2em;
            }

            a {
                color: rgb(93, 0, 190);
                text-decoration: none;
            }

            ul{
                li{
                    margin-bottom: 5px;
                    position: relative;
                    padding-left: 13px;
                    margin-left: 5px;

                    &:before {
                        content: '•';
                        position: absolute;
                        left: 0;
                    }
                }
            }
        }

        .dependancies-group {
            margin-top: 12px;
            z-index: 3;
            position: relative;

            li {
                display: inline-block;

                .icon-view-show {
                    position: relative;
                    top: 2px;
                    font-size: 1.2em;
                    margin-right: 0px;
                }

                button {
                    position: relative;
                    top: 0px;
                    right: 0px;
                    user-select: none;
                    margin-bottom: 6px;
                    cursor: pointer;
                    height: 24px;

                    .button-body {
                        display: inline-block;
                        transition: all 0.1s;
                        background: rgb(255, 255, 255);
                        border: 1px solid rgb(177, 177, 177);
                        font-size: 0.7em;
                        border-radius: 10px;
                        padding: 3px 4px 5px 8px;
                        margin-right: 6px;
                        cursor: pointer;
                        z-index: 1;
                    }

                    &::-moz-focus-inner {
                        border: 0;
                    }

                    &.loading,
                    &.selected {
                        &.depends {
                            .button-body {
                                background: rgb(255, 241, 0);
                            }

                            &:not(.alt) {
                                .button-body {
                                    border: 1px solid rgb(176, 172, 0);
                                }
                            }

                            &.alt {
                                &:after {
                                    right: 0px;
                                    top: 9px;
                                }
                            }
                        }

                        &.dependancy {
                            .button-body {
                                background: rgb(3, 228, 232);
                            }

                            &:not(.alt) {
                                .button-body {
                                    border: 1px solid rgb(0, 110, 113);
                                }
                            }

                            &.alt {
                                .button-body {
                                    box-shadow: inset 0 0 0 1px rgb(0, 110, 113);
                                }
                            }
                        }
                    }

                    &.loading {
                        .icon {
                            &:before {
                                content: "\e901";
                                animation: spinner-rotate 0.5s infinite linear;
                                display: inline-block;
                                line-height: 0.8em;
                            }
                        }
                    }

                    &.selected {
                        .icon {
                            &:before {
                                content: "\e902";
                            }
                        }

                        &:not(:focus) {
                            &:after {
                                content: '';
                                position: absolute;
                                height: 5px;
                                top: 9px;
                                display: inline-block;
                            }

                            &.depends {
                                &:after {
                                    right: 6px;
                                    width: 1px;
                                    background: rgb(255, 243, 0);
                                }
                            }

                            &.dependancy {
                                &:after {
                                    width: 2px;
                                    left: -1px;
                                    background: rgb(3, 228, 232);
                                }
                            }
                        }
                    }

                    &:not(.selected) {
                        &.viewable {
                            &.depends {
                                &:hover {
                                    .button-body {
                                        border:1px solid rgb(208, 198, 30);
                                        background: mix(rgb(255, 255, 255), rgb(255, 241, 0), 20%);
                                    }
                                }
                            }

                            &.dependancy {
                                &:hover {
                                    .button-body {
                                        border: 1px solid rgb(22, 199, 204);
                                        background: mix(rgb(255, 255, 255), rgb(3, 228, 232), 20%);
                                    }
                                }
                            }
                        }
                    }

                    &.depends,
                    &.dependancy {
                        &:focus {
                            outline: none;

                            &:before{
                                content: '';
                                position: absolute;
                                top: -4px;
                                left: -5px;
                                width: calc(100% + 4px);
                                height: 33px;
                                display: block;
                                z-index: -1;
                                border-radius: 14px;
                            }
                            
                        }
                        &.loading {
                            z-index: 9999999 !important;
                            &:before{
                                content: '';
                                position: absolute;
                                top: 0px;
                                left: 0px;
                                width: calc(100% - 6px);
                                height: 24px;
                                display: block;
                                z-index: -1;
                                border-radius: 14px;
                            }
                        }
                    }

                    &.depends{
                        &.loading {
                            &:before{
                                animation: pulsePackageDepancyLoading 0.7s infinite;
                                box-shadow: 0 0 0 0 rgba(255, 243, 0, .5);
                            }
                        }
                        &.viewable:focus {
                            .button-body {
                                border-color: rgb(199, 191, 0);
                            }
                            &:before{
                                background: rgb(255, 243, 0);
                            }
                        }
                        &:not(.viewable):focus {
                            .button-body {
                                text-decoration:underline;
                            }
                        }
                    }

                    &.dependancy{
                        &.loading {
                            &:before{
                                animation: pulsePackageDependsLoading 0.7s infinite;
                                box-shadow: 0 0 0 0 rgba(3, 228, 232, .5);
                            }
                        }
                        &.viewable:focus {
                            .button-body{
                                border-color:rgb(1, 147, 150);
                            }
                            &:before{
                                background: rgb(3, 228, 232);
                            }
                        }
                    }

                    &:not(.viewable) {
                        .button-body {
                            padding: 5px 9px 5px 8px;
                            background: rgba(255, 255, 255, 0.7);
                            border-color: rgba(0, 0, 0, 0);
                            cursor: default;
                        }
                        .icon{
                            display: none;
                        }
                    }
                }

                &.alternatives {
                    margin-bottom: 5px;
                    margin-right: 5px;

                    button {
                        border: none;
                        margin-bottom: 0px;

                        &:not(.viewable) {
                            .button-body {
                                box-shadow: none;
                                background: rgba(219, 170, 255, 0);
                                padding-left: 0px;
                                padding-right: 0px;
                            }
                        }


                        &:focus {
                            &:before{
                                width: calc(100% + 9px);
                            }
                        }
                    }

                    > span {
                        border: 1px dashed rgb(226, 226, 226);
                        display: inline-block;
                        border-radius: 16px;
                        padding: 0px;
                        background: rgba(255, 255, 255, 0.48);
                    }

                    li {
                        button {
                            margin-right: 0px;
                            .button-body {
                                margin-right: 0px;
                            }

                            &:not(.viewable) {
                                &:last-child {
                                    .button-body {
                                        padding-right: 5px;
                                    }
                                }
                            }

                            &.selected{
                                &:not(:focus) {
                                    &.depends{
                                        &:after{
                                            right: -1px;
                                            width: 3px;
                                        }
                                    }
                                }
                            }
                        }

                        &:not(:first-of-type) {
                            &:before {
                                content: "or";
                                font-size: 0.8em;
                                margin-right: 5px;
                                margin-left: 5px;
                            }
                        }
                    }
                }
            }
        }

        &.removing {
            transform: translateY(15px);
            opacity: 0 !important;
        }

        &.has-package {
            display: block;
            opacity: 1;
            top: 0px;
        }

        &:not(.has-package) {
            .package {
                display: none;
            }
        }

        &:focus {
            outline: none;

            .focus-shadow {
                background: rgb(226, 226, 226);
                border: 1px solid rgb(206, 206, 206);
                border-radius: 3px;
                position: absolute;
                top: -8px;
                left: -8px;
                z-index: -1;
                width: calc(100% + 16px);
                min-width: 336px;
                height: calc(100% + 16px);
            }
        }

    }

    html.lte-650{
        .dpkg-package {
            width:100%;
            .package {
                width:100%;
                
            }
        }
    }

    html.browser-firefox {
        .dpkg-package {
            .package-details-container {
                margin-bottom: -9px;
                padding-bottom: 5px;
            }

            &::-moz-focus-inner {
                border: 0;
            }
        }
    }

</style>