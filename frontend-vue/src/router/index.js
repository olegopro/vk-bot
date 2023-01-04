import { createRouter, createWebHistory } from 'vue-router'
import Accounts from '../views/Accounts.vue'
import Tasks from '../views/Tasks.vue'

const routes = [
    {
        path: '/',
        name: 'Home',
        component: Accounts,
        meta: {
            layout: 'main'
        }
    },
    {
        path: '/tasks',
        name: 'Tasks',
        component: Tasks,
        meta: {
            layout: 'main'
        }
    }
]

const router = createRouter({
    history: createWebHistory(process.env.BASE_URL),
    routes,
    linkActiveClass: 'active'
})

export default router
