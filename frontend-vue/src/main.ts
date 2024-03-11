import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import PerfectScrollbar from 'vue3-perfect-scrollbar'
import { VueMasonryPlugin } from 'vue-masonry'
import { createPinia } from 'pinia'
import { initResizeHandler } from '@/handlers/resizeHandler'

import 'vue3-perfect-scrollbar/dist/vue3-perfect-scrollbar.css'
import './assets/scss/main.css'
import 'bootstrap'
import 'bootstrap-icons/font/bootstrap-icons.css'
import 'notyf/notyf.min.css'

// Обработчик обновления высоты
initResizeHandler()

const pinia = createPinia()

createApp(App)
    .use(VueMasonryPlugin)
    .use(router)
    .use(PerfectScrollbar)
    .use(pinia)
    .mount('#app')
