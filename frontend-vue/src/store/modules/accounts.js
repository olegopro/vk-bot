import axios from 'axios'

export default {
    namespaced: true,

    state() {
        return {
            accounts: []
        }
    },

    mutations: {
        addAccount(state, account) {
            state.accounts = account
        }
    },

    actions: {
        async accounts({ commit }) {
            const { data } = await axios.post('http://localhost:8080/api')
            console.log(data)
            commit('addAccount', data)
        }
    },

    getters: {
        getAccounts(state) {
            return state.accounts
        }
    }
}
