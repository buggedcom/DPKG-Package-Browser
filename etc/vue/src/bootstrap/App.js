'use strict';

import Vue from 'vue';
import App from '../components/App';

export default {
    setup() {
        new Vue({
            el: '#app',
            template: '<App/>',
            components: { App }
        });

        document.body.classList.remove('loading');
    }
}

