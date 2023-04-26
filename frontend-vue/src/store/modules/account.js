import axios from 'axios'
import axiosThrottle from 'axios-request-throttle'

export default {
    namespaced: true,

    state() {
        return {
            account: [],
            ownerData: [],
            accountFollowers: {},
            accountFriends: {},
            accountFriendsCount: [],
            accountNewsFeed: [],
            nextFrom: null
        }
    },

    mutations: {
        addAccount(state, account) {
            state.account = account
        },

        addOwnerData(state, { accountData, friendsCount }) {
            const index = state.ownerData.findIndex((item) => item.id === accountData.id)

            if (index !== -1) {
                // Объект с таким же идентификатором уже существует, обновляем его
                state.ownerData[index] = { ...state.ownerData[index], ...accountData, friends_count: friendsCount }
            } else {
                // Добавляем новый объект в массив
                state.ownerData.push({ ...accountData, friends_count: friendsCount })
            }
        },

        addAccountFollowers(state, accountFollowers) {
            state.accountFollowers = accountFollowers
        },

        addAccountFriends(state, accountFriend) {
            state.accountFriends = accountFriend
        },

        addAccountFriendsCount(state, accountFriendCounts) {
            state.accountFriendsCount = state.accountFriendsCount.concat(accountFriendCounts)
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

        async ownerData({ commit }, id) {
            console.log(id)
            const [accountDataResponse, friendsCountResponse] = await Promise.all([
                axios.post(`http://localhost:8080/api/account/data/${id}`),
                axios.post(`http://localhost:8080/api/account/friends/count/${id}`)
            ])
            const accountData = accountDataResponse.data.response[0]
            const friendsCount = friendsCountResponse.data.response.count
            commit('addOwnerData', { accountData, friendsCount })
        },

        async groupData({ commit }, id) {
            const { data } = await axios.post(`http://localhost:8080/api/group/data/${Math.abs(id)}`)
            commit('addOwnerData', { accountData: data.response[0] })
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

        getOwnerData(state) {
            // return state.accountData.find(user => user.id === 9121607)
            return state.ownerData
        },

        getOwnerDataById: (state) => (id) => {
            if (!state.ownerData.length) {
                console.log('Data is not available yet')
                return null
            }

            return state.ownerData.find(user => user.id === Math.abs(id))
        },

        getAccountFollowers(state) {
            return state.accountFollowers
        },

        getAccountFriends(state) {
            return state.accountFriends
        },

        getAccountFriendsCount: (state) => (id) => {
            return state.accountFriendsCount.find(user => user.id === id)
        },

        getAccountNewsFeed(state) {
            return state.accountNewsFeed
        },

        getNextFrom(state) {
            return state.nextFrom
        }
    }
}
