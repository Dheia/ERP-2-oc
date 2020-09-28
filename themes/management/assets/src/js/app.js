window._ = require('lodash');
window.moment = require('moment');
window.Dropzone = require('dropzone');
window.$ = window.jQuery = require('jquery');
window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Vue from 'vue'
import store from '~/store'
import router from '~/router'
import i18n from '~/plugins/i18n'
import App from '~/components/App'

// 3rd party
import vuetify from '~/plugins/vuetify' // path to vuetify export
import InstantSearch from 'vue-instantsearch';

// common
import eventBus from './common/functions/Event';
import formatters from './common/functions/Formatters';

Vue.use(formatters);
Vue.use(eventBus);
Vue.use(InstantSearch);

import '~/plugins'
import '~/components'

Vue.config.productionTip = false;

/* eslint-disable no-new */
new Vue({
    vuetify,
    i18n,
    store,
    router,
    ...App
}).$mount('#app');
