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
            commit('addAccounts', data)
            console.log(data)
        }
    },

    getters: {
        getAccounts(state) {
            return state.accounts
        }
    }
}
