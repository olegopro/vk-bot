import { createStore } from 'vuex'
import accounts from './modules/accounts'
import tasks from './modules/tasks'

export default createStore({
    state: {},
    getters: {},
    mutations: {},
    actions: {},
    modules: {
        accounts, tasks
    }
})
