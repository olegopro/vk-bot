import { createStore } from 'vuex'
import accounts from './modules/accounts'
import tasks from './modules/tasks'
import account from './modules/account'
import settings from './modules/settings'

export default createStore({
    state: {},
    getters: {},
    mutations: {},
    actions: {},
    modules: {
        account, accounts, tasks, settings
    }
})
