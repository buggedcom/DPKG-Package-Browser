'use strict';

const tabIndexes = {
    /**
     * This contains the tabindex hierarchy used with KeyboardInteraction.tabIndex().
     * This should NOT be referenced directly.
     *
     * @author Oliver Lillie
     */
    search: {
        input: 1,
        cancelFilter: 1
    },
    list: {
        item: 1
    },
    package: {
        box: 1,
        close: 2,
        dependancy: 1
    }
};

/**
 * This function is used for maintaining a tabindex hierarchy within the app.
 * Whenever adding tab indexes this should be used to grab the revelant hierarchy value.
 *
 * @author Oliver Lillie
 * @param id string
 * @param subid1 string
 * @param subid2 string
 * @param subid3 string
 * @return integer
 */
export function tabIndex(id, subid1, subid2, subid3) {
    const id_type = typeof tabIndexes[id];
    if(id_type === 'number') {
        return tabIndexes[id];
    }

    if(id_type === 'object') {
        if(typeof subid1 !== 'undefined') {
            const subid1_type = typeof tabIndexes[id][subid1];
            if(subid1_type === 'number') {
                return tabIndexes[id][subid1];
            }

            if(subid1_type === 'object') {
                const subid2_type = typeof tabIndexes[id][subid1][subid2];
                if(subid2_type === 'number') {
                    return tabIndexes[id][subid1][subid2];
                }

                if(subid2_type === 'object') {
                    const subid3_type = typeof tabIndexes[id][subid1][subid2][subid3];
                    if(subid3_type === 'number') {
                        return tabIndexes[id][subid1][subid2][subid3];
                    }
                }
            }
        }
    }

    console.error('missing tab index reference for: ' + (id + ' ' + subid1 + ' ' + subid2 + ' ' + subid3).trim()); // allow in git hook

    return 999;
}

/**
 * Finds and focuses the first widget or focusable (not disabled) node within the given node.
 *
 * @author Oliver Lillie
 * @param node Element The element to search within.
 * @return widget|Element|null Returns a widget or element that is focused. If nothing is focused
 *  it returns null.
 */
export function focusFirstFocusableNode(node) {
    const focusable = Array.from(node.querySelectorAll('button:not([disabled]), [tabindex]:not([tabindex="-1"]):not([disabled])'));

    let element = null;
    focusable.some(
        (subnode) => {
            if(subnode.offsetHeight !== 0) { // if the offsetHeight is === 0 then the element is not visible
                subnode.focus();
                element = subnode;
                return true;
            }
        }
    );

    return element;
}
