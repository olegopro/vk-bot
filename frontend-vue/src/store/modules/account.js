import axios from 'axios'
import axiosThrottle from 'axios-request-throttle'

export default {
    namespaced: true,

    state() {
        return {
            account: [],
            accountData: [],
            accountFollowers: {},
            accountFriends: {},
            accountFriendsCount: {},
            accountNewsFeed: [],
            nextFrom: null
        }
    },

    mutations: {
        addAccount(state, account) {
            state.account = account
        },

        addAccountData(state, accountData) {
            console.log(accountData)
            const index = state.accountData.findIndex(
                (item) => item.id === accountData.id
            )

            if (index !== -1) {
                // Объект с таким же идентификатором уже существует, обновляем его
                state.accountData[index] = accountData
            } else {
                // Добавляем новый объект в массив
                state.accountData.push(accountData)
            }
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

        async addAccountNewsFeed(state, accountNewsFeed) {
            state.accountNewsFeed = state.accountNewsFeed.concat(accountNewsFeed)
            // state.accountNewsFeed = [...state.accountNewsFeed, ...accountNewsFeed]
        },

        cleanAccountNewsFeed(state) {
            state.accountNewsFeed = []
        },

        addNextFrom(state, nextFrom) {
            state.nextFrom = nextFrom
        }
    },

    actions: {
        async account({ commit }, id) {
            const { data } = await axios.get(`http://localhost:8080/api/accounts/${id}`)
            commit('addAccount', data)
        },

        async accountData({ commit }, id) {
            let data // объявляем переменную data заранее

            if (id < 0) {
                ({ data } = await axios.post(`http://localhost:8080/api/group/data/${Math.abs(id)}`))
            } else {
                ({ data } = await axios.post(`http://localhost:8080/api/account/data/${id}`))
            }

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

        async addPostsToLike({ rootState }, accountId) {
            const { data } = await axios.post('http://localhost:8080/api/account/get-posts-for-like', null, {
                params: {
                    account_id: accountId
                }
            })

            rootState.tasks.tasks = [...data]
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
                console.warn(error)
            }
        },

        async accountNewsfeed({ commit }, { accountID, startFrom = null }) {
            const { data } = await axios.post('http://localhost:8080/api/account/newsfeed', null, {
                params: {
                    account_id: accountID,
                    start_from: startFrom
                }
            })

            const result = await data.response.items.filter(item => item.attachments[0]?.type === 'photo')
            commit('addNextFrom', data.response.next_from)

            if (startFrom === null) commit('cleanAccountNewsFeed')
            commit('addAccountNewsFeed', result)
        },

        async addLike(_, { accountId, ownerId, itemId }) {
            const { data } = await axios.post('http://localhost:8080/api/account/like', null, {
                params: {
                    account_id: accountId,
                    owner_id: ownerId,
                    item_id: itemId
                }
            })

            return data
        },

        async getScreenNameById({ commit }, accountId) {
            const localAxios = axios
            axiosThrottle.use(localAxios, { requestsPerSecond: 1.5 })

            const { data } = await localAxios.post('http://localhost:8080/api/account/get-screen-name-by-id', null, {
                params: {
                    user_id: accountId
                }
            })

            return data.response
        }
    },

    getters: {
        getAccount(state) {
            return state.account
        },

        getAccountData(state) {
            // return state.accountData.find(user => user.id === 9121607)
            return state.accountData
        },

        getAccountDataById: (state) => (id) => {
            if (!state.accountData.length) {
                console.log('Data is not available yet')
                return null // или вернуть любое другое значение, которое вы считаете подходящим
            }

            return state.accountData.find(user => user.id === Math.abs(id))
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
        },

        getNextFrom(state) {
            return state.nextFrom
        }
    }
}
