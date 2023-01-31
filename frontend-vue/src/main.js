import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import { VueMasonryPlugin } from 'vue-masonry'

import 'bootstrap/dist/css/bootstrap.min.css'
import './assets/css/main-overflow.css'
import 'bootstrap'

createApp(App)
    .use(store)
    .use(router)
    .use(VueMasonryPlugin)
    .mount('#app')
