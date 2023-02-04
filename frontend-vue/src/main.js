import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import { VueMasonryPlugin } from 'vue-masonry'
import PerfectScrollbar from 'vue3-perfect-scrollbar'

import 'vue3-perfect-scrollbar/dist/vue3-perfect-scrollbar.css'
import './assets/scss/main.css'
import 'bootstrap'

createApp(App)
    .use(store)
    .use(router)
    .use(VueMasonryPlugin)
    .use(PerfectScrollbar)
    .mount('#app')
