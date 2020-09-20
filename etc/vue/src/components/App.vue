<template>
    <div id="app">

        <Search ref="search"
                @filter="handleSearch"
                @clear="handleSearchClear"
                @submit="handleSearchSubmit"
                @loseFocus="handleSearchLoseFocus"
        />

        <LogoResponsive ref="logo" />

        <div class="dpkg-body-container">

            <List ref="list" @packageSelected="handleListPackageSelected" />

            <div class="dpkg-browers-container">
                <div class="dpkg-browsers-scroller">
                    <Browser v-for="(packageList, index) in getOrderedBrowsers"
                             :data-key="packageList[0].packageName"
                             ref="browser"
                             :index="index"
                             :key="packageList[0].packageName"
                             :class="{ 'top': index === 0 }"
                             :packages.sync="packageList"
                             :style="getBrowserStyle(index)"
                             @packageLoaded="handlePackageLoaded"
                             @dependantClicked="handleDependantClicked"
                             @browserEmptied="() => removeBrowser(index)"
                    />
                </div>
            </div>

        </div>
        
        <footer>
            <div class="api">
                <span class="api-label">Backend</span>
                <toggle-button
                    ref="toggle"
                    :value.sync="getApiToggleButtonInitialCheckedState"
                    :sync="true"
                    :css-colors="true"
                    :width="50"
                    :height="15"
                    :labels="getApiToggleButtonLabels"
                    @input="handleApiChange"
                />
            </div>
            <div class="author">
                <span class="name">Oliver Lillie</span>
                <a href="mailto:buggedcom@gmail.com?subject=dpkg%20browser" class="email">buggedcom@gmail.com</a>
            </div>
        </footer>
    </div>
</template>

<script>
'use strict';

import Search from './Search.vue';
import List from './List.vue';
import Browser, { BrowserPackage } from './Browser.vue';
import { path } from '../helpers/Helpers';
import { tabIndex } from '../helpers/KeyboardInteraction';
import { ToggleButton } from 'vue-js-toggle-button';
import LogoResponsive from "./LogoResponsive";

Vue.use(ToggleButton);

export default {
    data: () => ({
        /**
         * A list of BrowserPackage arrays.
         *
         * @author Oliver Lillie
         * @type Array[Array[BrowserPackage[]]]
         */
        browsers: [],

        apiType: null,

        /**
         * @type MediaQueryList
         */
        mediaQuery: null
    }),

    provide() {
        return {
            /**
             * Direct access to the list to each browser so there are not
             * $parent.$parent chains in the packages.
             *
             * @author Oliver Lillie
             */
            getList: () => this.$refs.list,

            /**
             * Direct access to the root app so that the list can directly
             * access the top app without convoluted $parent calls.
             *
             * @author Oliver Lillie
             */
            getApp: () => this,

            /**
             * Provide getPath from App so the api can easily access the base
             * path that the curernt api is using.
             *
             * @author Oliver Lillie
             */
            getPath: () => this.getPath()
        }
    },

    components: {
        Search,
        List,
        Browser,
        LogoResponsive,
        ToggleButton
    },

    watch: {
        apiType(value, oldValue) {
            if(oldValue) {
                localStorage.setItem('apiType', value);
            }
        }
    },

    methods: {

        /**
         * @see Helpers.path
         */
        path: path,

        /**
         * @see KeyboardInteraction.tabIndex
         */
        tabIndex: tabIndex,

        handleSearch(event) {
            const search = event.target.value;

            // this.$refs.logo.hidden = !!search;
            this.$refs.list.search = search;
        },

        handleSearchClear(event) {
            // this.$refs.logo.hidden = false;
            this.$refs.list.search = '';
        },

        handleSearchSubmit(event) {
            this.$refs.list.focusfirstVisibleItem();
        },

        handleSearchLoseFocus(event) {
            this.$refs.list.focusfirstVisibleItem();
        },

        getPath() {
            return path(this.apiType, process.env.NODE_ENV);
        },

        /**
         * Handles the api toggle button state change and sets the apps backend
         * api that the frontend uses.
         *
         * @author Oliver Lillie
         */
        handleApiChange(checked) {
            this.apiType = checked === false ? 'node' : 'php';
        },

        /**
         * Handles the event from a Package component that is propagated up to
         * the Browser component when a packages json data has been loaded from
         * the server and the package has been rendered.
         *
         * If the events currentPackage has a parentPackage the parentPackage's
         * connected via the down arrow to the currentPackage.
         *
         * The List component is also notified about the packages load so that
         * the loading list item can be set to not loading.
         *
         * @author Oliver Lillie
         * @see Browser.methods.packageLoaded
         * @see Package.watch.pkg
         * @listens module:Browser~packageLoaded
         * @param event
         */
        handlePackageLoaded(event) {
            // this must be done in next tick to ensure that the package has dom
            // positioning in order to have the connector correctly position.
            this.$nextTick(() => {
                if(event.currentPackage.parentPackage) {
                    event.currentPackage.parentPackage.connectDown(event);
                }
                this.$refs.list.packageLoaded(event.currentPackage.packageName);
            });
        },

        /**
         * Handles the selection of a package within the list component.
         * It removes all the current package browsers and then adds a new one
         * that starts with the selected package.
         *
         * @author Oliver Lillie
         * @see List.watch.selectedPackage
         * @param {Object} event
         * @listens module:List~packageSelected
         */
        handleListPackageSelected(event) {
            this.removeBrowsers();
            this.$nextTick(() => this.addPackageBrowser(event.packageName));
        },

        /**
         * Handles the click of a dependant (Used by) package pill box from in a
         * Package component that is propagated up from inside a Browser
         * component.
         *
         * The function adds another package browser below the source browser
         * that starts with the clicked dependant package.
         *
         * @see Package.methods.clickDependant
         * @see Browser.methods.handleDependantClicked
         * @author Oliver Lillie
         * @param {Object} event
         */
        handleDependantClicked(event) {
            this.addPackageBrowser(event.packageName, event.fromPackage, event.fromPackage.$parent);
        },

        /**
         * Adds a new package browser into the App that starts with the package
         * sourced from the packageName argument.
         *
         * @param {string} packageName The name of the package that is to start
         *  the new package browser.
         * @param {Package=} parentPackage The parent package component that
         *  has requested (typically through `handleDependantClicked`) a new
         *  package browser.
         * @param {Browser=} parentBrowser The parent package's parent
         *  Browser component.
         */
        addPackageBrowser(packageName, parentPackage, parentBrowser) {
            const parentIndex = parentBrowser ? parentBrowser.index : null;

            const data = [
                new BrowserPackage(
                    packageName,
                    null,
                    parentPackage || null,
                    parentBrowser || null,
                    parentIndex,
                    null,
                    null
                )
            ];

            this.browsers.splice(parentIndex + 1, 0, data);
        },

        /**
         * Removes all the package browsers from the application.
         *
         * @author Oliver Lillie
         */
        removeBrowsers() {
            this.browsers = [];
        },

        /**
         * Removes a specific browser component form the application at the
         * given index of the browsers array.
         *
         * @author Oliver Lillie
         * @param {int} index
         */
        removeBrowser(index) {
            this.browsers.splice(index, 1);
            if(this.browsers.length === 0) {
                this.$refs.list.selectedPackage = null;
            }
        },

        /**
         * Provides access to the top browser instance.
         *
         * @author Oliver Lillie
         */
        getTopBrowser() {
            return this.$refs.browser[0];
        }
    },

    /**
     * Setup the initial apiType value.
     *
     * @author Oliver Lillie
     */
    beforeMount() {
        if (localStorage.apiType) {
            this.apiType = localStorage.apiType;
        }
    },

    computed: {

        getOrderedBrowsers() {
            return this.browsers;
        },

        /**
         * Returns the initial checked state of the backend switch toggle.
         *
         * @author Oliver Lillie
         */
        getApiToggleButtonInitialCheckedState() {
            return (localStorage.apiType || 'php') === 'php';
        },

        /**
         * Returns the button labels for the API toggle.
         *
         * @author Oliver Lillie
         */
        getApiToggleButtonLabels() {
            return {
                checked: 'PHP',
                unchecked: 'Node'
            };
        },

        /**
         * Returns the inline styles required for each browser component.
         *
         * In order for the down connector arrows to be correct laid over the
         * top of the browsers below, a decreasing z-index is applied.
         *
         * In order to position the browser in relation to the selected list
         * item node, a margin top is applied to the browser too.
         *
         * @author Oliver Lillie
         * @return {Function}
         */
        getBrowserStyle() {
            return (index) => {
                const zindex = 'z-index:' + (99999 - index) + ';';

                if(index > 0 || this.browsers.length === 0) {
                    return zindex;
                }

                const packageName = this.browsers[index][0].packageName;
                const list_item_node = this.$refs.list.getPackageNode(packageName);
                if(!list_item_node) {
                    return zindex;
                }

                return zindex + ' margin-top: ' + (list_item_node.offsetTop + 10) + 'px;';
            }
        }
    }
}
</script>

<style lang="scss">
// generic html resets
html, body, div, span, applet, object, iframe,
h1, h2, h3, h4, h5, h6, p, blockquote, pre,
a, abbr, acronym, address, big, cite, code,
del, dfn, em, img, ins, kbd, q, s, samp,
small, strike, strong, sub, sup, tt, var,
b, u, i, center,
dl, dt, dd, ol, ul, li,
fieldset, form, label, legend,
table, caption, tbody, tfoot, thead, tr, th, td,
article, aside, canvas, details, embed,
figure, figcaption, footer, header, hgroup,
menu, nav, output, ruby, section, summary,
time, mark, audio, video {
    margin: 0;
    padding: 0;
    border: 0;
    font-size: 100%;
    font: inherit;
    vertical-align: baseline;
}
article, aside, details, figcaption, figure,
footer, header, hgroup, menu, nav, section {
    display: block;
}
body {
    line-height: 1;
}
ol, ul {
    list-style: none;
}
blockquote, q {
    quotes: none;
}
blockquote:before, blockquote:after,
q:before, q:after {
    content: '';
    content: none;
}
table {
    border-collapse: collapse;
    border-spacing: 0;
}
*{
    box-sizing: border-box;
}

// button reset
button,
input,
optgroup,
select,
textarea {
    font-family: inherit;
    font-size: 100%;
    line-height: 1.15;
    margin: 0;
    border:0;
    padding:0;
}

/**
 * Show the overflow in IE.
 * 1. Show the overflow in Edge.
 */

button,
input { /* 1 */
    overflow: visible;
}

/**
 * Remove the inheritance of text transform in Edge, Firefox, and IE.
 * 1. Remove the inheritance of text transform in Firefox.
 */

button,
select { /* 1 */
    text-transform: none;
}

/**
 * Correct the inability to style clickable types in iOS and Safari.
 */

button,
[type="button"],
[type="reset"],
[type="submit"] {
    text-align: left;
    background: rgba(0, 0, 0, 0);

    &:hover{
        color:#000;
    }
}

// body defaults
body{
    font-family: 'Quicksand', sans-serif;

    // this hides everything whilst things are being loaded.
    &.loading{
        display:none;
    }
}

//spinner rotation animation
@keyframes spinner-rotate {
    0% {
        transform: rotate(0);
    }
    100% {
        transform: rotate(360deg);
    }
}
.icon-spinner {
    animation: spinner-rotate 0.5s infinite linear;
    display: inline-block;
}

// toasts
.toasted.toasted-error {
    background: rgb(255, 0, 0);
    padding: 10px 15px 10px 15px;
    border-radius: 18px;
    color: rgb(255, 255, 255);
    border: 2px solid rgb(160, 0, 0);
    text-shadow: 0 0 2px rgb(0, 0, 0);
}

// app
html {
    min-height:100%;
    position:relative;
}
body{
    overflow:hidden;
    height:100%;
    background: rgb(241, 241, 241);
}
.dpkg-body-container {
    padding-top: 50px;
    width: 100%;
    height:100vh;
    display: flex;
    overflow: auto;
}
.dpkg-browers-container {
    min-width: calc(100% - 250px);
    padding-top: 6px;
}

#app {
    position:absolute;
    top:0;
    bottom:0;
    left:0;
    right:0;
    height: 100%;
    overflow: hidden;
}

footer{

    .author,
    .api {
        position: fixed;
        right: 10px;
        font-size: 0.7em;
        z-index: 999999;
        padding: 4px;
        border-radius: 5px;
        opacity: 0.2;
        transition: all 0.2s;

        &:hover {
            opacity: 1;
        }
    }

    .author{
        bottom: 10px;

        a {
            cursor: pointer;
            color: #000;
        }

    }

    .api {
        bottom: 27px;

        .vue-js-switch {
            top: -1px !important;

            .v-switch-core {
                background: rgb(243, 243, 243);
                box-shadow: 0 0 0 1px rgb(216, 216, 216);
            }

            .v-switch-button {
                background: rgb(255, 243, 0) !important;
                box-shadow: 0 0 0 1px rgb(214, 205, 0);
                top: 0px !important;
            }

            .v-switch-label {
                color: #000 !important;
                font-weight: normal;
            }

            &.toggled {
                .v-switch-button {
                    background: rgb(185, 85, 255) !important;
                    box-shadow: 0 0 0 1px rgb(142, 50, 199);
                }
            }
        }
    }
}

html.lte-950 {
    .dpkg-browers-container {
        transition: all 0.2s ease-out;
    }

    .dpkg-list {
        &:not(:focus-within) {
            &:not(.forced-open).has-selection,
            &.forced-closed {
                + .dpkg-browers-container {
                    margin-left: -230px;
                }
            }
        }
    }

}

html.lte-425 {
    .dpkg-list {
        &:not(:focus-within) {
            &:not(.forced-open).has-selection,
            &.forced-closed {
                + .dpkg-browers-container {
                    margin-left: 25px;
                    left:0%;
                    width:100%;
                }
            }
        }
    }
}

</style>