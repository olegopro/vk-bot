import axios from 'axios'

export default {
    namespaced: true,

    state() {
        return {
            account: [],
            accountData: {},
            accountFollowers: {}
        }
    },

    mutations: {
        addAccount(state, account) {
            state.account = account
        },

        addAccountData(state, accountData) {
            state.accountData = accountData
        },

        addAccountFollowers(state, accountFollowers) {
            state.accountFollowers = accountFollowers
        }
    },

    actions: {
        async account({ commit }, id) {
            const { data } = await axios.get(`http://localhost:8080/api/accounts/${id}`)
            commit('addAccount', data)
        },

        async accountData({ commit }, id) {
            const { data } = await axios.post(`http://localhost:8080/api/account/data/${id}`)
            commit('addAccountData', data.response[0])
        },

        async accountFollowers({ commit }, id) {
            const { data } = await axios.post(`http://localhost:8080/api/account/followers/${id}`)
            commit('addAccountFollowers', data.response.items)
        }
    },

    getters: {
        getAccount(state) {
            return state.account
        },

        getAccountData(state) {
            return state.accountData
        },

        getAccountFollowers(state) {
            return state.accountFollowers
        }
    }
}
