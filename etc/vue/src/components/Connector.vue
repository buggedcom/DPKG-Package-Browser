<template>

    <div role="presentation" class="dpkg-connector" :class="[getDirectionClass, getPositionedClass]">
        <span ref="barFromOrigin" class="bar from-origin"></span>
        <span ref="barConnector" class="bar vertical"></span>
        <span ref="barToTarget" class="bar to-target"></span>
        <span ref="head" class="head"></span>
    </div>

</template>

<script>
'use strict';

import { isInBreakPoint } from "../helpers/Helpers";

/**
 * @module Package
 */
export default {

    data() {
        return {
            /**
             * Determines if the connector has had its initial position set.
             * This is only relevant for the initial position because of the
             * initial set animations. This flag prevents any subsequent
             * position updates from restarting those animations.
             *
             * @author Oliver Lillie
             * @type boolean
             */
            positioned: false
        };
    },

    props: {
        /**
         * The origin node of the connected to start positioning from.
         *
         * @author Oliver Lillie
         * @type {Element}
         */
        origin: Element,

        /**
         * The target node of the connected to positioning to.
         *
         * @author Oliver Lillie
         * @type {Element}
         */
        target: Element,

        /**
         * The direction mode of the connector. Either "down" or "right"
         *
         * @author Oliver Lillie
         * @type {String}
         */
        direction: String
    },

    watch: {
        /**
         * If the origin changes a re-layout is forced.
         *
         * @author Oliver Lillie
         */
        origin() {
            this.layout();
        },

        /**
         * If the target changes a re-layout is forced.
         *
         * @author Oliver Lillie
         */
        target() {
            this.layout();
        }
    },

    methods: {
        /**
         * Lays out the connector and sets the positioning of the connectors
         * elements based on the direction prop.
         *
         * @author Oliver Lillie
         */
        layout() {
            if(this.direction === 'right') {
                if(isInBreakPoint('lte-650') === true) {
                    this._layoutRightAndDown();
                } else {
                    this._layoutRightAndUp();
                }
            } else if(this.direction === 'down') {
                this._layoutDown();
            }

            // 400ms is the time of the "in" animation takes and is therefore
            // only considered positioned after the animation has been
            // completed.
            setTimeout(() => this.positioned = true, 400);
        },

        /**
         * Returns the bounding x and y positions of the given node.
         *
         * @author Oliver Lillie
         * @param {Element} node
         * @return {{x: number, y: number}}
         * @private
         */
        _getNodeGlobalOffset(node) {
            const rect = node.getBoundingClientRect();

            return {
                x: rect.left,
                y: rect.top
            };
        },

        /**
         * Returns the local position for the given global x and y positions in
         * the given container.
         *
         * @author Oliver Lillie
         * @param {Element} containerNode
         * @param {int} globalX
         * @param {int} globalY
         * @return {{x: number, y: number}}
         * @private
         */
        _getGlobalPositionToLocalPosition(containerNode, globalX, globalY) {
            const containerBox = containerNode.getBoundingClientRect();

            return {
                x: globalX - containerBox.x,
                y: globalY - containerBox.y
            };
        },

        /**
         * Sets the layout of the connectors elements to display the connector
         * as connecting right.
         *
         * @author Oliver Lillie
         * @private
         */
        _layoutRightAndUp() {
            const originPosition = this._getNodeGlobalOffset(this.origin);
            const originBox = this.origin.getBoundingClientRect();
            const localCoordsOfOrigin = this._getGlobalPositionToLocalPosition(this.$parent.$el, originPosition.x, originPosition.y);
            const targetPosition = this._getNodeGlobalOffset(this.target);

            const barFromOriginWidth = targetPosition.x - originPosition.x - originBox.width + 5;
            const barFromOriginX = (localCoordsOfOrigin.x + originBox.width - 13);
            const barFromOriginY = (localCoordsOfOrigin.y + 8);
            this.$refs.barFromOrigin.style.width = barFromOriginWidth + 'px';
            this.$refs.barFromOrigin.style.left = barFromOriginX + 'px';
            this.$refs.barFromOrigin.style.top = barFromOriginY + 'px';

            const barConnectorHeight = barFromOriginY - 19;//(detect().name === 'firefox' ? 19 : 19);
            const barConnectorX = (barFromOriginX + barFromOriginWidth - 7);
            this.$refs.barConnector.style.height = barConnectorHeight + 'px';
            this.$refs.barConnector.style.left = barConnectorX + 'px';
            this.$refs.barConnector.classList.add('in');

            this.$refs.barToTarget.style.left = barConnectorX + 'px';
            this.$refs.barToTarget.classList.add('in');

            const barToTargetComputedStyle = getComputedStyle(this.$refs.barToTarget);
            const barToTargetWidth = parseInt(barToTargetComputedStyle.width, 10);
            const barToTargetX = parseInt(barToTargetComputedStyle.width, 10);
            this.$refs.head.style.left = (barConnectorX + barToTargetX + barToTargetWidth + 21) + 'px';
            this.$refs.head.classList.add('in');
        },

        /**
         * Sets the layout of the connectors elements to display the connector
         * as connecting right.
         *
         * @author Oliver Lillie
         * @private
         */
        _layoutRightAndDown() {
            const originPosition = this._getNodeGlobalOffset(this.origin);
            const originBox = this.origin.getBoundingClientRect();
            const localCoordsOfOrigin = this._getGlobalPositionToLocalPosition(this.$parent.$el, originPosition.x, originPosition.y);
            const targetPosition = this._getNodeGlobalOffset(this.target);

            const barFromOriginWidth = targetPosition.x - originPosition.x - originBox.width + 4;
            const barFromOriginX = (localCoordsOfOrigin.x + originBox.width - 13);
            const barFromOriginY = (localCoordsOfOrigin.y + 8);
            this.$refs.barFromOrigin.style.width = barFromOriginWidth + 'px';
            this.$refs.barFromOrigin.style.left = barFromOriginX + 'px';
            this.$refs.barFromOrigin.style.top = barFromOriginY + 'px';

            const barConnectorHeight = targetPosition.y - originPosition.y;
            const barConnectorY = barFromOriginY + 6;
            this.$refs.barConnector.style.height = barConnectorHeight + 'px';
            this.$refs.barConnector.style.top = barConnectorY + 'px';
            this.$refs.barConnector.classList.add('in');
            this.$nextTick(() => this.$refs.barConnector.classList.add('setup'));

            const barToTargetY = barConnectorY + barConnectorHeight - 1;
            this.$refs.barToTarget.style.top = barToTargetY + 'px';
            this.$refs.barToTarget.classList.add('in');

            this.$refs.head.style.top = (barToTargetY + 4) + 'px';
            this.$refs.head.classList.add('in');
        },

        /**
         * Sets the layout of the connectors elements to display the connector
         * as connecting downwards.
         *
         * @author Oliver Lillie
         * @private
         */
        _layoutDown() {
            const originPosition = this._getNodeGlobalOffset(this.origin);
            const localCoordsOfOrigin = this._getGlobalPositionToLocalPosition(this.$parent.$el, originPosition.x, originPosition.y);
            const targetPosition = this._getNodeGlobalOffset(this.target);

            const barFromOriginY = (localCoordsOfOrigin.y + 8);
            const barFromOriginWidth = (localCoordsOfOrigin.x + 15);
            this.$refs.barFromOrigin.style.top = barFromOriginY + 'px';
            this.$refs.barFromOrigin.style.width = barFromOriginWidth + 'px';

            const barConnectorHeight = targetPosition.y - originPosition.y;
            const barConnectorY = barFromOriginY + 6;
            this.$refs.barConnector.style.height = barConnectorHeight + 'px';
            this.$refs.barConnector.style.top = barConnectorY + 'px';
            this.$refs.barConnector.classList.add('in');
            this.$nextTick(() => this.$refs.barConnector.classList.add('setup'));

            const barToTargetY = barConnectorY + barConnectorHeight - 1;
            this.$refs.barToTarget.style.top = barToTargetY + 'px';
            this.$refs.barToTarget.classList.add('in');

            this.$refs.head.style.top = (barToTargetY + 4) + 'px';
            this.$refs.head.classList.add('in');
        }
    },

    computed: {

        /**
         * Computes the connection direction class.
         *
         * @author Oliver Lillie
         * @return {string}
         */
        getDirectionClass() {
            return 'connects-' + this.direction + ' and-' + (isInBreakPoint('lte-650') === true ? 'down' : 'up');
        },

        /**
         * Computes the connections positioned class.
         *
         * @author Oliver Lillie
         * @return {boolean|string}
         */
        getPositionedClass() {
            return this.positioned === true && 'positioned';
        }
    },

    /**
     * Forces the connector to layout upon mount.
     *
     * @author Oliver Lillie
     */
    mounted() {
        this.layout();
    }
};

</script>

<style lang="scss">

    .dpkg-connector {
        position: absolute;
        top: 0px;
        left: 0px;
        z-index: 2;
        transform-origin: top;
        transition: all 0.2s;

        .bar {
            display: block;

            &.vertical {
                position: relative;
                z-index: 2;
                width: 7px;
                border: 1px solid transparent;
                border-top: none;
                border-bottom: none;
                transform: scaleY(0);
            }

            &.from-origin,
            &.to-target {
                width: 0px;
                z-index: 1;
                position: absolute;
                height: 7px;
                border: 1px solid transparent;
            }
        }

        .head {
            width: 0;
            height: 0;
            display: inline-block;
            position: absolute;
            border-color: transparent;
            -webkit-transform: rotate(360deg);
            top: -14px;
            left: 10px;
            z-index: 0;

            &:after,
            &:before {
                content: '';
                display: block;
                position: absolute;
                left: -12px;
                width: 0;
                height: 0;
                border-style: solid;
                border-color: transparent;
            }

            &:before {
                top: -11px;
                border-width: 11px 13px 11px 11px;
                left: -13px;
            }

            &:after {
                top: -9px;
                border-width: 9px;
            }
        }

        &.connects-right {
            .bar {
                background: rgb(255, 241, 0);
                border-color: rgb(177, 171, 1);

                &.from-origin {
                    border-left: none;
                    transition: width 0.1s;
                }

                &.vertical {
                    opacity: 0;
                    transition: all 0.2s 0.1s, opacity 0s 0.1s;

                    &.in {
                        opacity: 1;
                        transform: scaleY(1);
                    }
                }

                &.to-target {
                    border-right: none;
                    opacity: 0;
                    transition: width 0.05s 0.3s, opacity 0s 0.3s;

                    &.in {
                        opacity: 1;
                        width: 11px;
                    }
                }
            }
            .head {
                opacity: 0;
                transition: margin-left 0.05s 0.35s, opacity 0.05s 0.35s;

                &.in {
                    opacity: 1;
                }

                &:before {
                    border-left-color: rgb(177, 171, 1);
                }

                &:after {
                    border-left-color: rgb(255, 241, 0);
                }
            }

            &.and-up {
                .bar {
                    &.from-origin {
                        top: 1px;
                    }

                    &.vertical {
                        top: 20px;
                        transform-origin: bottom;
                    }

                    &.to-target {
                        top: 14px;
                    }
                }

                .head {
                    top: 17px;
                    margin-left: -5px;

                    &.in {
                        margin-left: 0px;
                    }
                }
            }

            &.and-down {
                .bar {
                    &.from-origin {
                        top: 1px;
                    }

                    &.vertical {
                        top: 20px;
                        transform-origin: bottom;
                    }

                    &.to-target {
                        top: 14px;
                    }
                }

                .head {
                    top: 17px;
                    margin-left: -5px;

                    &.in {
                        margin-left: 0px;
                    }

                    &:after,
                    &:before {
                        content: '';
                        display: block;
                        position: absolute;
                        left: -12px;
                        width: 0;
                        height: 0;
                        border-style: solid;
                        border-color: transparent;
                    }

                    &:before {
                        top: -11px;
                        border-width: 11px 13px 11px 11px;
                        left: -13px;
                    }

                    &:after {
                        top: -9px;
                        border-width: 9px;
                    }
                }
            }
        }

        &.connects-down {
            .bar {
                background: rgb(39, 228, 233);
                border-color: rgb(0, 143, 146);

                &.from-origin {
                    left: -15px;
                    transition: width 0.05s;
                }

                &.vertical {
                    left: -15px;
                    opacity: 0;

                    &.in {
                        transition: all 0.2s 0.05s, opacity 0s 0.05s;
                        opacity: 1;
                        transform: scaleY(1);
                        transform-origin: top;
                    }
                }

                &.to-target {
                    left: -15px;
                    border-right: none;
                    opacity: 0;
                    transition: width 0.05s 0.25s, opacity 0s 0.25s;

                    &.in {
                        opacity: 1;
                        width: 11px;
                    }
                }
            }

            .head {
                left: 8px;
                margin-left: -5px;
                opacity: 0;
                transition: margin-left 0.05s 0.3s, opacity 0.05s 0.3s;

                &.in {
                    margin-left: 0px;
                    opacity: 1;
                }

                &:before {
                    border-left-color: rgb(0, 143, 146);
                }

                &:after {
                    border-left-color: rgb(3, 228, 232);
                }
            }

        }

        &.positioned * {
            transition: none !important;
        }
    }

</style>