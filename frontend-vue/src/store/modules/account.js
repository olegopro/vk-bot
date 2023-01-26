import axios from 'axios'

export default {
    namespaced: true,

    state() {
        return {
            account: [],
            accountData: {},
            accountFollowers: {},
            accountFriends: {},
            accountFriendsCount: {},
            accountNewsFeed: {}
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
        },

        addAccountFriends(state, accountFriend) {
            state.accountFriends = accountFriend
        },

        addAccountFriendsCount(state, accountFriendCounts) {
            state.accountFriendsCount = accountFriendCounts
        },

        addAccountNewsFeed(state, accountNewsFeed) {
            state.accountNewsFeed = accountNewsFeed
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
            commit('addAccountFollowers', data.response)
        },

        async accountFriends({ commit }, id) {
            const { data } = await axios.post(`http://localhost:8080/api/account/friends/${id}`)
            commit('addAccountFriends', data.response)
        },

        async accountFriendsCount({ commit }, id) {
            const { data } = await axios.post(`http://localhost:8080/api/account/friends/count/${id}`)
            commit('addAccountFriendsCount', data.response)
        },

        async addAccount({ rootState }, accessToken) {
            const { data } = await axios.post('http://localhost:8080/api/account/add', null, {
                params: {
                    access_token: accessToken
                }
            })

            rootState.accounts.accounts.push(data)
        },

        async deleteAccount({ rootState, rootMutations }, id) {
            try {
                await axios.delete(`http://localhost:8080/api/accounts/${id}`)

                const accounts = rootState.accounts.accounts

                accounts.splice(
                    accounts.findIndex(key => key.id === id),
                    1
                )
            } catch (error) {
                console.log(error)
            }
        },

        async accountNewsfeed({ commit }, accountID) {
            const { data } = await axios.post('http://localhost:8080/api/account/newsfeed', null, {
                params: {
                    account_id: accountID
                }
            })
            const result = data.response.items.filter(item => item.attachments[0]?.type === 'photo')

            commit('addAccountNewsFeed', result)
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
        },

        getAccountFriends(state) {
            return state.accountFriends
        },

        getAccountFriendsCount(state) {
            return state.accountFriendsCount
        },

        getAccountNewsFeed(state) {
            return state.accountNewsFeed
        }
    }
}
