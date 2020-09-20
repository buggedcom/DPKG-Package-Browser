<template>
    <div class="dpkg-search" tabindex="-1" :class="{ filtered, focused: inputFocused }" @click="handleClick">
        <input
            spellcheck="false"
            :tabindex="tabIndex('search', 'input')"
            ref="input"
            type="text"
            v-model="search"
            @focus="handleFilterFocus"
            @blur="handleFilterBlur"
            @keyup="handleFilterKeyUp"
            @keydown.enter="handleFilterEnter"
            @keydown.esc="handleFilterEscape"
        />
        <span class="icon-search" @click="focus"></span>
        <div class="underline">
            <span ref="sizer" class="background">{{ search }}{{ inputFocused ? ' ' : '' }}</span>
            <button aria-label="Clear search" :tabindex="tabIndex('search', 'cancelFilter')" class="icon-close" @click="clearFilter"></button>
        </div>

    </div>
</template>

<script>
'use strict';

import { tabIndex, focusFirstFocusableNode } from '../helpers/KeyboardInteraction';

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
        'getList'
    ],

    data: () => ({

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

        inputFocused: false

    }),

    watch: {
        search(value) {
            this.$refs.input.style.width = this.$refs.sizer.offsetWidth + 15 + 'px';
            this.getList().forcedOpenState = value.length === 0 ? 'closed' : 'open';
        }
    },

    methods: {

        /**
         * @see KeyboardInteraction.tabIndex
         */
        tabIndex: tabIndex,

        handleClick(event) {
            if(event.target !== this.$el) {
                return;
            }

            this.getList().forcedOpenState = this.getList().forcedOpenState !== 'open' ? 'open' : 'closed';
        },

        /**
         * Handlers enter key event when the search input is focused. When enter
         * is pressed, the first available package item in the list is focused.
         *
         * @author Oliver Lillie
         * @param {KeyboardEvent} event
         */
        handleFilterEnter(event) {
            this.$emit('submit', event);
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
                this.$emit('loseFocus', event);
            } else {
                this.clearFilter();
            }
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
            this.$emit('filter', event);
        },

        handleFilterFocus() {
            this.inputFocused = true;
        },

        handleFilterBlur() {
            this.inputFocused = false;
        },

        /**
         * Clears the current search filter.
         *
         * @author Oliver Lillie
         */
        clearFilter(event) {
            this.filtered = false;
            this.search = '';
            this.$refs.input.focus();
            this.$emit('clear', event || {});
        },

        focus(event) {
            this.$refs.input.focus();
        }
    },

    /**
     * Forces mount of the List component to focus the loading throbber for
     * accessibility.
     *
     * @author Oliver Lillie
     */
    mounted() {
        this.$refs.input.focus();
    }

};

</script>

<style lang="scss">

    .dpkg-search {
        position: fixed;
        width: 100%;
        z-index: 9999999;
        background: rgb(104, 0, 193);
        border-bottom: 1px solid rgb(79, 0, 146);
        height: 50px;
        outline: none;

        .icon-search {
            color: rgba(255, 255, 255, 0.45);
            font-size: 1.8em;
            position: absolute;
            top: 11px;
            left: 10px;
            cursor: text;
        }

        input {
            font-size: 1em;
            padding: 15px 0px 15px 15px;
            max-width: calc(100% - 258px);
            color: rgb(255, 255, 255);
            background: rgba(0, 0, 0, 0);
            font-family: 'Quicksand', sans-serif;

            &:focus {
                outline: none;

                + .icon-search {
                    display: none;
                }
            }
        }

        .underline {
            display: none;
            z-index: -1;
            background-color: rgb(137, 0, 255);
            height: 35px;
            position: absolute;
            left: 7px;
            top: 7px;
            transform: scale(0, 1);
            transition: all 0.3s ease-out;
            transform-origin: left;
            max-width: calc(100% - 230px);
            padding-right: 35px;

            span.background {
                font-size: 1em;
                padding: 15px 5px 15px 15px;
                visibility: hidden;
            }
        }

        .icon-close {
            color: rgba(255, 255, 255, 0.4);
            font-size: 1.8em;
            display: none;
            position: absolute;
            top: -1px;
            right: -2px;
            cursor: pointer;
            transition: all 0.1s;
            padding: 5px;


            &:before {
                position: relative;
                top: 1px;
            }

            &:hover {
                color: rgba(255, 255, 255, 1);
                transform: scale(1.2);
                transform-origin: center;
            }

            &:focus {
                outline: none;
                color: rgba(255, 255, 255, 1);
                background: rgb(195, 125, 255);
                border-radius: 40px;
            }
        }

        &.focused {
            .underline {
                transform: scale(1);
                display: inline-block;
            }
        }

        &.filtered {
            input {
                &:focus {
                    + .underline {
                        display: inline-block;
                        transform: scale(1);
                    }
                }
            }

            .icon-search {
                display: none;
            }

            .underline {
                transform: scale(1);
                display: inline-block;
            }

            .icon-close {
                display: inline-block;
            }
        }
    }

    html.lte-500 {
        .dpkg-search {
            input {
                max-width: calc(100% - 90px);
            }

            .underline {
                max-width: calc(100% - 60px);
            }

            input{
                position: relative;
                left:55px;
            }
            .underline{
                left:60px;
            }
            .icon-search {
                left:55px;
                &:before {
                    position: relative;
                    left:-5px;
                }
            }

            &:before {
                content: "";
                position: absolute;
                left: 12px;
                top: 15px;
                width: 30px;
                height: 3px;
                background: rgba(255, 255, 255, 0.5);
                box-shadow: 0 8px 0 0 rgba(255, 255, 255, 0.5), 0 16px 0 0 rgba(255, 255, 255, 0.5);
            }

            &:focus {
                &:before {
                    background: rgba(255, 255, 255, 1);
                    box-shadow: 0 8px 0 0 rgba(255, 255, 255, 1), 0 16px 0 0 rgba(255, 255, 255, 1);
                }
            }
        }
    }

</style>