'use strict';

/**
 * Handles port switching for switching between apis.
 *
 * @author Oliver Lillie
 * @param {string} apiType Either 'php' or 'node'.
 * @param {string} nodeEnv Either 'production' or 'development'.
 * @return {string}
 */
export function path(apiType, nodeEnv) {
    // if(nodeEnv === 'production') {
    //     if(apiType === 'php') {
    //         return window.location.protocol + '//' + window.location.hostname + ':80';
    //     }
    //     return window.location.protocol + '//' + window.location.hostname + ':8080';
    // }

    if(apiType === 'php') {
        return window.location.protocol + '//' + window.location.hostname + ':80';
    }
    return window.location.protocol + '//' + window.location.hostname + ':8080';
}

export function isInBreakPoint(breakpoint) {
    return document.documentElement.classList.contains(breakpoint);
}