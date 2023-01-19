import { createStore } from 'vuex'
import accounts from './modules/accounts'
import tasks from './modules/tasks'
import account from './modules/account'

export default createStore({
    state: {},
    getters: {},
    mutations: {},
    actions: {},
    modules: {
        account, accounts, tasks
    }
})
