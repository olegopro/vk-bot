import axios from 'axios'

export default {
    namespaced: true,

    state() {
        return {
            accounts: []
        }
    },

    mutations: {
        addAccounts(state, accounts) {
            state.accounts = accounts
        }
    },

    actions: {
        async accounts({ commit }) {
            const { data } = await axios.post('http://localhost:8080/api/accounts')
            console.log(data)
            commit('addAccounts', data)
        }
    },

    getters: {
        getAccounts(state) {
            return state.accounts
        }
    }
}
