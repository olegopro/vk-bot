import { defineStore } from 'pinia'
import axios from 'axios'
import axiosThrottle from 'axios-request-throttle'
import { showErrorNotification } from '../helpers/notyfHelper'

export const useAccountStore = defineStore('account', {
    state: () => ({
        account: [],
        ownerData: [],
        accountFollowers: {},
        accountFriends: {},
        accountFriendsCount: [],
        accountNewsFeed: [],
        nextFrom: null,
        previousNextFrom: null,
        isLoadingFeed: false
    }),

    actions: {
        async fetchAccount(id) {
            const { data } = await axios.get(`http://localhost:8080/api/accounts/${id}`)
            const index = this.account.findIndex((item) => item.id === data.id)

            if (index !== -1) {
                // Объект с таким же идентификатором уже существует, обновляем его
                this.account[index] = { ...this.account[index], ...data }
            } else {
                // Добавляем новый объект в массив
                this.account.push(data)
            }
        },

        async fetchOwnerData(id) {
            const [accountDataResponse, friendsCountResponse] = await Promise.all([
                axios.post(`http://localhost:8080/api/account/data/${id}`),
                axios.post(`http://localhost:8080/api/account/friends/count/${id}`)
            ]).catch(error => showErrorNotification(error))

            const accountData = accountDataResponse.data.response[0]
            const friendsCount = friendsCountResponse.data.response.count
            this.ownerData.push({ ...accountData, friends_count: friendsCount })
        },

        async fetchAccountFollowers(accountId) {
            axios.post(`http://localhost:8080/api/account/followers/${accountId}`)
                .then(response => this.accountFollowers[accountId] = response.data.response.items)
        },

        async fetchAccountFriends(accountId) {
            axios.post(`http://localhost:8080/api/account/friends/${accountId}`)
                .then(response => this.accountFriends[accountId] = response.data.response.items)
        },

        async fetchAccountFriendsCount(id) {
            const { data } = await axios.get(`http://localhost:8080/api/account/friends/count/${id}`)
            this.accountFriendsCount = data
        },

        async fetchAccountNewsFeed(accountID, startFrom = null) {
            if (startFrom !== null && startFrom === this.previousNextFrom) {
                return
            }

            this.previousNextFrom = startFrom

            const localAxios = axios
            axiosThrottle.use(localAxios, { requestsPerSecond: 5 })

            localAxios.post('http://localhost:8080/api/account/newsfeed', {
                account_id: accountID,
                start_from: startFrom
            })
                .then(response => {
                    const data = response.data
                    const result = data.response.items.filter(item => item.attachments[0]?.type === 'photo')
                    this.nextFrom = data.response.next_from

                    if (startFrom === null) {
                        this.accountNewsFeed = []
                    }

                    this.accountNewsFeed = [...this.accountNewsFeed, ...result]
                    this.isLoadingFeed = false
                })
                .catch(() => this.fetchAccountNewsFeed(accountID, this.nextFrom))
        },

        async addLike(accountId, ownerId, itemId) {
            const { data } = await axios.post('http://localhost:8080/api/account/like', {
                account_id: accountId,
                owner_id: ownerId,
                item_id: itemId
            })

            return data
        },

        async getScreenNameById(accountId) {
            const { data } = await axios.post('http://localhost:8080/api/account/get-screen-name-by-id', {
                user_id: accountId
            })

            return data.response
        },

        async addPostsToLike(accountId, taskCount) {
            const { data } = await axios.post('http://localhost:8080/api/account/get-posts-for-like', {
                account_id: accountId,
                task_count: taskCount
            })

            this.accountNewsFeed = [...this.accountNewsFeed, ...data]
        },

        async groupData(id) {
            const { data } = await axios.post(`http://localhost:8080/api/group/data/${Math.abs(id)}`)
            this.addOwnerData(data.response[0])
        },

        addOwnerData(accountData) {
            const index = this.ownerData.findIndex((item) => item.id === accountData.id)

            if (index !== -1) {
                // Объект с таким же идентификатором уже существует, обновляем его
                this.ownerData[index] = { ...this.ownerData[index], ...accountData }
            } else {
                // Добавляем новый объект в массив
                this.ownerData.push(accountData)
            }
        },

        async getAccountDetails(ownerId) {
            await axios.get(`http://localhost:8080/api/account/${ownerId}`)
                .then(response => {
                    return response.data
                })
                .catch(error => showErrorNotification(error.response.data.message))
        }
    },

    getters: {
        getAccountFollowers: (state) => (accountId) => state.accountFollowers[accountId] || [],
        getAccountFriends: (state) => (accountId) => state.accountFriends[accountId] || [],
        getOwnerDataById: state => (id) => state.ownerData.find(user => user.id === Math.abs(id)),
        getAccountById: state => (id) => state.account.find(account => account.id === Math.abs(id))
    }
})
