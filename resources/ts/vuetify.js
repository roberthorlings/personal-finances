// src/plugins/vuetify.js

import '@mdi/font/css/materialdesignicons.css'
import Vue from 'vue'
import Vuetify from 'vuetify/lib'
import colors from 'vuetify/lib/util/colors'

Vue.use(Vuetify);

export default new Vuetify({
    icons: {
        iconfont: 'mdi', // default - only for display purposes
    },
    theme: {
        themes: {
            light: {
                primary: colors.deepPurple.lighten3,
                secondary: colors.red.base,
            },
        },
    },
});
