import { createRouter, createWebHistory } from 'vue-router'
import Accounts from '../views/Accounts.vue'
import Tasks from '../views/Tasks.vue'
import Account from '../views/Account.vue'
import Statistics from '../views/Statistics.vue'
import Settings from '../views/Settings.vue'

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
    },
    {
        path: '/account/:id',
        name: 'Account',
        component: Account,
        meta: {
            layout: 'main'
        }
    },
    {
        path: '/statistics',
        name: 'Statistics',
        component: Statistics,
        meta: {
            layout: 'main'
        }
    },
    {
        path: '/settings',
        name: 'Settings',
        component: Settings,
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
