'use strict';

import { detect } from 'detect-browser';

export default {
    setup() {
        const browser = detect();
        document.documentElement.classList.add('browser-' + browser.name);
    }
}

