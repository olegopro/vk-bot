import { defineStore } from 'pinia'
import axios from 'axios'

export const useAccountStore = defineStore('account', {
    state: () => ({
        account: [],
        ownerData: [],
        accountFollowers: {},
        accountFriends: {},
        accountFriendsCount: [],
        accountNewsFeed: [],
        nextFrom: null,
        previousNextFrom: null
    }),

    actions: {
        async fetchAccount(id) {
            const { data } = await axios.get(`http://localhost:8080/api/accounts/${id}`)
            this.account = data
        },

        async fetchOwnerData(id) {
            const [accountDataResponse, friendsCountResponse] = await Promise.all([
                axios.post(`http://localhost:8080/api/account/data/${id}`),
                axios.post(`http://localhost:8080/api/account/friends/count/${id}`)
            ])
            const accountData = accountDataResponse.data.response[0]
            const friendsCount = friendsCountResponse.data.response.count
            this.ownerData = { ...accountData, friends_count: friendsCount }
        },

        async fetchAccountFollowers(id) {
            const { data } = await axios.get(`http://localhost:8080/api/account/followers/${id}`)
            this.accountFollowers = data
        },

        async fetchAccountFriends(id) {
            const { data } = await axios.get(`http://localhost:8080/api/account/friends/${id}`)
            this.accountFriends = data
        },

        async fetchAccountFriendsCount(id) {
            const { data } = await axios.get(`http://localhost:8080/api/account/friends/count/${id}`)
            this.accountFriendsCount = data
        },

        async fetchAccountNewsFeed(id, startFrom = null) {
            const { data } = await axios.get(`http://localhost:8080/api/account/newsfeed/${id}`, {
                params: { start_from: startFrom }
            })
            this.accountNewsFeed = data
            this.nextFrom = data.next_from
        },

        async addLike(accountId, ownerId, itemId) {
            const { data } = await axios.post('http://localhost:8080/api/account/like', null, {
                params: {
                    account_id: accountId,
                    owner_id: ownerId,
                    item_id: itemId
                }
            })
            return data
        },

        async getScreenNameById(accountId) {
            const { data } = await axios.post('http://localhost:8080/api/account/get-screen-name-by-id', null, {
                params: {
                    user_id: accountId
                }
            })
            return data.response
        },

        async deleteAccount(id) {
            await axios.delete(`http://localhost:8080/api/accounts/${id}`)
            this.account = this.account.filter(account => account.id !== id)
        },

        async addPostsToLike(accountId, taskCount) {
            const { data } = await axios.post('http://localhost:8080/api/account/get-posts-for-like', null, {
                params: {
                    account_id: accountId,
                    task_count: taskCount
                }
            })
            this.accountNewsFeed = [...this.accountNewsFeed, ...data]
        }
    },

    getters: {
        getAccount() {
            return this.account
        },
        getOwnerData() {
            return this.ownerData
        },
        getAccountFollowers() {
            return this.accountFollowers
        },
        getAccountFriends() {
            return this.accountFriends
        },
        getAccountFriendsCount() {
            return this.accountFriendsCount
        },
        getAccountNewsFeed() {
            return this.accountNewsFeed
        },
        getNextFrom() {
            return this.nextFrom
        },
        getPreviousNextFrom() {
            return this.previousNextFrom
        },

        getOwnerDataById: (state) => (id) => {
            return state.ownerData.find(user => user.id === Math.abs(id))
        }
    }
})
