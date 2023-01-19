import { createRouter, createWebHistory } from 'vue-router'
import Accounts from '../views/Accounts.vue'
import Tasks from '../views/Tasks.vue'
import Account from '../views/Account.vue'
import ActionsHistory from '../views/ActionsHistory.vue'
import SystemLogs from '../views/SystemLogs.vue'

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
        path: '/history',
        name: 'History',
        component: ActionsHistory,
        meta: {
            layout: 'main'
        }
    },
    {
        path: '/logs',
        name: 'Logs',
        component: SystemLogs,
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
