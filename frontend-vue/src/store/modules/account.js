import axios from 'axios'

export default {
    namespaced: true,

    state() {
        return {
            account: []
        }
    },

    mutations: {
        addAccount(state, account) {
            state.account = account
        }
    },

    actions: {
        async account({ commit }, id) {
            const { data } = await axios.get(`http://localhost:8080/api/accounts/${id}`)
            commit('addAccount', data)
        }
    },

    getters: {
        getAccount(state) {
            return state.account
        }
    }
}
