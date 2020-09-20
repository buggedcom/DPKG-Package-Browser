<template>
    <div
        class="dpkg-list"
        :class="{ loading, 'has-selection': selectedPackage, 'forced-open': forcedOpenState === 'open', 'forced-closed' : forcedOpenState === 'closed', empty: filterListIsEmpty }"
        v-hammer:swipe.left.right="handleSwipe"
    >
        <div class="empty-search" :class="{ show: filterListIsEmpty }" :style="getEmptySearchWidthStyles">
            <span class="icon-alert"></span> There are no packages matching your current search.
        </div>

        <div class="package-list">

            <ol ref="list" role="list">
                <li role="listitem"
                    ref="pkg"
                    v-for="(pkg_item, index) in filteredList"
                    :data-packagename="pkg_item.packageName"
                    :class="{ selected: pkg_item.packageName === selectedPackage, loading: pkg_item.packageName === loadingPackage }"
                >
                    <button
                        :aria-label="pkg_item.packageName"
                        :aria-describedby="'listitem-' + pkg_item.packageName + '-description'"
                        @keydown.up="handleShiftTab"
                        @keydown.down="handleTab"
                        @keydown.right="(event) => clickPackage(event, pkg_item)"
                        :tabindex="tabIndex('list', 'item')"
                        @click="(event) => clickPackage(event, pkg_item.packageName)"
                        :aria-busy="pkg_item.packageName === loadingPackage && 'true'"
                    >
                        <span class="name">{{ pkg_item.packageName }}</span>
                        <span :id="'listitem-' + pkg_item.packageName + '-description'" role="definition" class="summary">{{ pkg_item.summary }}</span>
                        <span aria-hidden="true" class="icon icon-arrow-long-right"></span>
                        <span aria-hidden="true" class="icon icon-spinner"></span>
                    </button>
                </li>
            </ol>
        </div>

        <div ref="loading" tabindex="-1" class="loading" aria-busy="true">
            <span aria-hidden="true" class="icon-spinner"></span> Loading package list.
        </div>

    </div>
</template>

<script>
'use strict';

import Vue from 'vue';
import Toasted from 'vue-toasted';
import { isInBreakPoint } from "../helpers/Helpers"
import { tabIndex, focusFirstFocusableNode } from '../helpers/KeyboardInteraction';
import { VueHammer } from 'vue2-hammer';

Vue.use(Toasted);
Vue.use(VueHammer);

/**
 * The class of object that is used to generate the list items.
 *
 * @author Oliver Lillie
 */
class ListPackage {
    constructor(packageName, summary) {
        this.packageName = packageName;
        this.summary = summary;
    }
}

/**
 * @module List
 */
export default {

    inject: [
        /**
         * Inject getApp so that the list can directly access the top app
         * without convoluted $parent calls.
         *
         * @author Oliver Lillie
         */
        'getApp',

        /**
         * Inject getPath from App so the api can easily access the base path
         * that the curernt api is using.
         *
         * @author Oliver Lillie
         */
        'getPath'
    ],

    components: {
        VueHammer
    },

    data: () => ({

        /**
         * Contains the currently selected package reference.
         *
         * @author Oliver Lillie
         * @type {string|null}
         */
        selectedPackage: null,

        /**
         * Contains the currently loading package reference.
         *
         * @author Oliver Lillie
         * @type {string|null}
         */
        loadingPackage: null,

        /**
         * Controls the lists's initial loading state where the all the packages
         * are loaded into the List component.
         *
         * @author Oliver Lillie
         * @type {boolean}
         */
        loading: true,

        /**
         * Determines if the List component is currently in a filtered state
         * (ie is being searched via the input).
         *
         * @author Oliver Lillie
         * @type {boolean}
         */
        filtered: false,

        /**
         * The inital search value for the filter.
         *
         * @author Oliver Lillie
         * @type {string}
         */
        search: '',

        /**
         * The container for all the list packages.
         *
         * @author Oliver Lillie
         * @type {ListPackage[]}
         */
        packages: [],

        /**
         * When an item is selected we keep watch on the top position of the
         * item so that if the list is filtered we can update the browser
         * positions so they are relative to the new position of the list item.
         *
         * This is the interval id.
         *
         * @author Oliver Lillie
         * @see Package.watch.selectedPackage
         * @type {int|null}
         */
        selectedItemTopWatchInterval: null,

        /**
         * This is the current top position of the selected list item. It is
         * watched and so that if the list is filtered we can update the browser
         * positions so they are relative to the new position of the list item.
         *
         * The related interval for this is defined via
         * selectedItemTopWatchInterval.
         *
         * @author Oliver Lillie
         * @see Package.watch.selectedPackage
         * @type {int|null}
         */
        selectedItemTop: null,

        forcedOpenState: null,

        windowWidth: null
    }),

    watch: {

        /**
         * Handles the event propagation for when a package has been selected.
         *
         * @author Oliver Lillie
         * @fires module:List~packageSelected
         * @param {string} packageName
         */
        selectedPackage(packageName) {
            if(this.selectedItemTopWatchInterval) {
                clearInterval(this.selectedItemTopWatchInterval);
                this.selectedItemTopWatchInterval = null;
            }

            if(packageName === null) {
                return;
            }

            /**
             * Event reporting that the list has had a package selected.
             *
             * @author Oliver Lillie
             * @event module:List~packageSelected
             * @property {string} packageName The name of the package that has
             *  been selected.
             */
            this.$emit('packageSelected', {
                packageName: packageName
            });

            this.selectedItemTopWatchInterval = setInterval(() => {
                const node = this.getPackageNode(packageName);
                this.selectedItemTop = node && node.getBoundingClientRect().top
            }, 50);
        },

        /**
         * Watch for changes to the selected item top position so that the list
         * can trigger position changes in the top level browser so that the
         * browsers are always aligned with the selected list item.
         *
         * @author Oliver Lillie 
         */
        selectedItemTop() {
            this.getApp()
                .getTopBrowser()
                .positionRelativeToListNode(
                    this.getPackageNode(this.selectedPackage)
                );
        },

        /**
         * When packages is first set, then the list is no longer loading so
         * this watch toggles the loading boolean to trigger a redraw.
         *
         * Additionally we focus the input field when the packages are loaded
         * so the user can immediately start typing to search for the package
         * they want to browse. This is done on $nextTick() to ensure that the
         * input is visbile in the DOM because otherwise the input won't focus.
         *
         * @author Oliver Lillie
         */
        packages() {
            this.loading = false;
        }
    },

    methods: {

        /**
         * @see KeyboardInteraction.tabIndex
         */
        tabIndex: tabIndex,

        focusfirstVisibleItem() {
            focusFirstFocusableNode(this.$refs.list);
        },

        handleSwipe(event) {
            if(!this.selectedPackage) {
                this.forcedOpenState = null;
                return;
            }

            if(event.type === 'swipeleft') {
                this.forcedOpenState = 'closed';
            } else if(event.type === 'swiperight') {
                this.forcedOpenState = 'open';
            }

            event.preventDefault();
        },

        /**
         * Handlers escape key event when the search input is focused. When
         * escape is pressed, if there is no input then the first available
         * package item in the list is focused. However if there is input,
         * pressing escape simply clears the filter and refocuses the search
         * input.
         *
         * @author Oliver Lillie
         * @param {KeyboardEvent} event
         */
        handleFilterEscape(event) {
            if(this.search === '') {
                focusFirstFocusableNode(this.$refs.list);
            } else {
                this.clearFilter();
            }
        },

        /**
         * Handlers shift+tab events so that when pressed the previous list item
         * is focused.
         *
         * If the top of the list is reached, then the search input is focused.
         *
         * @author Oliver Lillie
         * @param {KeyboardEvent} event
         */
        handleShiftTab(event) {
            event.preventDefault();

            if(event.target.parentNode.previousSibling) {
                event.target.parentNode.previousSibling.querySelector('button').focus();
            } else {
                this.$refs.filter.focus();
            }
        },

        /**
         * Handlers tab events so that when pressed the next list item is
         * focused.
         *
         * If the bottom of the list is reached, nothing happens.
         *
         * @author Oliver Lillie
         * @param {KeyboardEvent} event
         */
        handleTab(event) {
            if(event.target.parentNode.nextSibling) {
                event.preventDefault();
                event.target.parentNode.nextSibling.querySelector('button').focus();
            }
        },

        handleWindowResize(event) {
            this.windowWidth = document.documentElement.clientWidth;
        },

        /**
         * A public accessor method for when a Package has bubbled up an event
         * to the app and the app can clear the loading status of the item that
         * has been requested.
         *
         * @author Oliver Lillie
         */
        packageLoaded() {
            this.clearLoadingStatus();
        },

        /**
         * Handles the click of a package in the list and sets private data to
         * trigger further event bubbles and reative redraws.
         *
         * @author Oliver Lillie
         * @param {Event} event
         * @param {string} packageName
         */
        clickPackage(event, packageName) {
            if(this.selectedPackage === packageName && isInBreakPoint('lte-950') === true) {
                event.preventDefault();
                return;
            }

            if(this.selectedPackage === packageName) {
                this.selectedPackage = null;
                this.loadingPackage = null;
                this.getApp().removeBrowsers();
                return;
            }

            this.selectedPackage = packageName;
            this.loadingPackage = packageName;
        },

        /**
         * A public accessor method for returning the node of the related
         * package.
         *
         * @author Oliver Lillie
         * @param {string} packageName
         * @return {Element}
         */
        getPackageNode(packageName) {
            return this.$refs.pkg.find((node) => node.dataset.packagename === packageName);
        },

        /**
         * Sets the browsers focus to the currently selected package. If not
         * package is selected then no focus takes place.
         *
         * @author Oliver Lillie
         */
        focusSelected() {
            if(!this.selectedPackage) {
                return;
            }

            const node = this.$refs.pkg.find((node) => node.dataset.packagename === this.selectedPackage);
            if(node) {
                node.querySelector('button').focus();
            }
        },

        /**
         * Clears the currently loading package to trigger a reactive redraw for
         * removing the loading status of the dom element.
         */
        clearLoadingStatus() {
            this.loadingPackage = null;
        },

        /**
         * Handles the list search input keyup event so that the filtered string
         * cause a reactive update of the listed packages.
         *
         * @author Oliver Lillie
         * @listens List:$refs.filter:keyUp
         * @param {Event} event
         */
        handleFilterKeyUp(event) {
            this.filtered = !!event.target.value;
        },

        /**
         * Clears the current search filter.
         *
         * @author Oliver Lillie
         */
        clearFilter() {
            this.filtered = false;
            this.search = '';
        },

        /**
         * Loads the list of packages from the server.
         *
         * When the packages have been loaded the `this.packages` data property
         * is updated causing a reactive update.
         *
         * @author Oliver Lillie
         */
        loadPackageList() {
            fetch(this.getPath() + "/packages").then((response) => response.json())
                .then((json) => {
                    if(!json) {
                        return;
                    }

                    if(json.status === false) {
                        Vue.toasted.show(
                            json.error.message,
                            {
                                theme: "toasted-error",
                                position: "bottom-center",
                                duration: 5000
                            }
                        );
                        return;
                    }

                    this.packages = Object.keys(json.data).map((pkg) => new ListPackage(pkg, json.data[pkg]));
                })
                .catch(error => {
                });
        }
    },

    /**
     * When the List component is created, we kickstart the load of the data and
     * we do this in the initial created hook so the data is ready asap.
     *
     * @author Oliver Lillie
     */
    created() {
        this.loadPackageList();
    },

    beforeMount() {
        this.windowWidth = document.documentElement.clientWidth;
        window.addEventListener('resize', this.handleWindowResize);
    },

    beforeDestroy() {
        window.removeEventListener('resize', this.handleWindowResize);
    },

    computed: {

        getEmptySearchWidthStyles() {
            return {
                width: (this.windowWidth) + 'px'
            };
        },

        /**
         * Returns the filtered list of packages using the input from the search
         * filter.
         *
         * @author Oliver Lillie
         * @return {ListPackage[]}
         */
        filteredList() {
            return this.packages.filter(
                (pkg) => pkg.packageName.toLowerCase().includes(this.search.toLowerCase())
                    || pkg.summary.toLowerCase().includes(this.search.toLowerCase())
                    || this.selectedPackage === pkg.packageName
            );
        },

        /**
         * Returns a boolean indicating if the search result is empty or not.
         * Any actively selected package is not included in this count.
         *
         * @return {boolean}
         */
        filterListIsEmpty() {
            return (this.packages.filter(
                (pkg) => pkg.packageName.toLowerCase().includes(this.search.toLowerCase())
                    || pkg.summary.toLowerCase().includes(this.search.toLowerCase())).length === 0);
        }
    }
};

</script>

<style lang="scss">

.dpkg-list {
    transition:all 0.2s ease-out, height 0s;
    min-width: 250px;
    z-index: 999999;
    height: 100%;
    padding: 10px;
    background: rgb(224, 198, 247);
    border-right: 1px solid rgb(196, 174, 216);

    .package-list {
    }

    [role="listitem"] button {
        padding: 8px;
        margin-bottom: 10px;
        border-radius: 4px;
        cursor: pointer;
        position: relative;
        width: 100%;
        outline: none;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1), background-color 0.3s ease;
        background: rgb(255, 255, 255);
        box-shadow: 0 0 0 1px rgba(0,0,0,.15), 0 2px 3px rgba(0,0,0,.2);

        .name{
            font-weight: 500;
        }

        .summary {
            display: block;
            font-size: 0.75em;
            margin-top: 3px;
            opacity: 0.8;
        }

        .icon-spinner {
            display: none;
        }

        .icon-arrow-long-right {
            display: inline-block;
        }

        .icon {
            position: absolute;
            top: 7px;
            right: 10px;
            opacity: 0.5;
        }

        &:after{
            transition: all 0.3s;
            opacity: 0;
            content: '';
            width: 5px;
            height: 12px;
            position: absolute;
            top: 9px;
            right: 30px;
        }

        &:focus {
            background: rgb(255, 251, 188);
        }

        &:hover {
            /*background: rgb(228, 188, 255);*/
            /*border: 1px solid rgb(208, 160, 236);*/

            .icon {
                opacity: 1;
            }
        }
    }

    li.loading {
        .icon {
            color: rgb(55, 13, 104);
        }

        .icon-spinner {
            display: inline-block;
        }

        .icon-arrow-long-right {
            opacity: 0;
            visibility: hidden;
        }
    }

    li.selected {

        button {
            background: rgb(255, 241, 0);

            &:not(.loading) .icon-arrow-long-right {
                color:rgb(255, 241, 0);
                text-shadow: 0px -1px rgb(156, 149, 0), 1px 0px rgb(156, 149, 0), 0px 1px rgb(156, 149, 0), -1px 0px rgb(156, 149, 0);
            }

            &:after{
                top: 11px;
                right: 0px;
                opacity: 1;
                background-color: rgb(255, 241, 0);
            }

            &:focus {
                background: rgb(255, 241, 0);
                box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.34), 0 2px 3px rgba(0, 0, 0, 0.44);

                .icon {
                    color:rgb(255, 241, 0);
                }

                &:after{
                    background-color: rgb(255, 241, 0);
                }
            }
        }

        &.loading {
            button {
                &:hover,
                &:focus {
                    .icon {
                        color: rgb(55, 13, 104);
                    }
                }
            }
        }

        &:not(.loading) {
            .icon-arrow-long-right {
                transition: all 0.2s;
                font-size: 2.3em;
                top: 0;
                right: -31px;
                opacity: 1;
                color: rgb(183, 100, 255);
            }
        }
    }

    &.empty {
        height: calc(100vh - 50px);
        
        .package-list {
            margin-top: 38px;
        }
    }

    &:not(.loading) {
        > .loading {
            display: none;
        }

        .empty-search {
            display: block;
            position: absolute;
            top: 0px;
            left: 0px;
            transition: all 0.2s, width 0s;
            background: rgb(249, 207, 74);
            padding: 10px;
            border-bottom: 1px solid rgb(179, 165, 119);

            &.show {
                top: 50px;
            }
        }
    }

}

.dpkg-list.loading {
    .package-list {
        display: none;
    }
}


html.lte-950 {
    .dpkg-list {
        position: absolute;
        width: 40%;
        padding:15px 10px 15px 10px;
        box-shadow: -5px 0px 5px 8px rgba(0, 0, 0, 0.24), 5px 0px 9px 8px rgba(0, 0, 0, 0.07);
        border-right: 1px solid rgb(176, 148, 202);

        &:not(:focus-within) {
            &:not(.forced-open).has-selection,
            &.forced-closed {
            }
        }

        li.selected:not(.loading) {
            .icon-arrow-long-right {
                font-size: 1.5em;
            }
        }

        [role="listitem"] button {
            padding: 10px;
            margin-bottom: 13px;
            font-size: 1.4em;

            .summary {
                font-size: 0.65em;
            }

            .icon-arrow-long-right {
                &:before{
                    content: "\e903";
                }
            }

            .icon {
                position: absolute;
                top: 7px;
                right: 10px;
                opacity: 0.5;
            }

            &.selected {
                .icon-arrow-long-right {
                }
            }

            &:focus {
            }

            &:hover {
                .icon {
                    opacity: 1;
                }
            }
        }

        &:not(:focus-within):not(.forced-open).has-selection,
            &.forced-closed {
                margin-left: calc(-100% - 25px);

                .selected {
                    .icon-arrow-long-right {
                        &:before {
                            content: "\e900";
                        }
                    }
                }
            }

        &:focus-within,
        &.forced-open{
            + .dpkg-browsers-container {
                margin-left: 0px;
                left: 100%;
                width:100%;
            }
        }

        &:not(.loading) {
            .empty-search {
                top: -100%;

                &.show {
                    top:0px;
                    width: 100vw !important;
                }
            }
        }
    }
}



</style>