'use strict';

export default {
    defaults: {
        '(max-width: 320px)': 320,
        '(max-width: 375px)': 375,
        '(max-width: 425px)': 425,
        '(max-width: 500px)': 500,
        '(max-width: 650px)': 650,
        '(max-width: 768px)': 768,
        '(max-width: 950px)': 950,
        '(max-width: 1024px)': 1024,
    },

    setup() {
        Object.keys(this.defaults).forEach(
            (breakPoint) => {
                const matchQuery = window.matchMedia(breakPoint);
                matchQuery.addListener(this.handleBrowserResize.bind(this));

                this.handleBrowserResize(matchQuery);
            }
        );
    },

    handleBrowserResize(matchQuery) {
        const breakPoint = this.defaults[matchQuery.media];
        const htmlNode = document.documentElement;
        htmlNode.classList.remove(`gt-${breakPoint}`, `lte-${breakPoint}`);
        htmlNode.classList.add(`${matchQuery.matches ? 'lte' : 'gte'}-${breakPoint}`);
    }
}
