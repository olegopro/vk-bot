import { createRouter, createWebHistory } from 'vue-router'
import RouterPaths from './routerPaths'
import Accounts from '../views/Accounts.vue'
import Tasks from '../views/Tasks.vue'
import Account from '../views/Account.vue'
import Statistics from '../views/Statistics.vue'
import Settings from '../views/Settings.vue'
import CyclicTasks from '../views/CyclicTasks.vue'

const routes = [
  {
    path: RouterPaths.home,
    name: 'Home',
    component: Accounts,
    meta: {
      layout: 'main'
    }
  },
  {
    path: RouterPaths.tasks(),
    name: 'Tasks',
    component: Tasks,
    meta: {
      layout: 'main'
    }
  },
  {
    path: RouterPaths.cyclicTasks(),
    name: 'CyclicTasks',
    component: CyclicTasks,
    meta: {
      layout: 'main'
    }
  },
  {
    path: RouterPaths.account(),
    name: 'Account',
    component: Account,
    meta: {
      layout: 'main'
    }
  },
  {
    path: RouterPaths.statistics,
    name: 'Statistics',
    component: Statistics,
    meta: {
      layout: 'main'
    }
  },
  {
    path: RouterPaths.settings,
    name: 'Settings',
    component: Settings,
    meta: {
      layout: 'main'
    }
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  linkActiveClass: 'active'
})

export default router
