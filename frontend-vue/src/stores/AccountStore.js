import { defineStore } from 'pinia'
import axios from '@/helpers/axiosConfig'
import axiosThrottle from 'axios-request-throttle'
import { showErrorNotification, showSuccessNotification } from '@/helpers/notyfHelper'

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
        isOwnerDataLoading: null,
        isLoadingFeed: false
    }),

    actions: {
        async fetchAccount(id) {
            const { data } = await axios.get(`accounts/${id}`)
            const index = this.account.findIndex((item) => item.id === data.id)

            if (index !== -1) {
                // Объект с таким же идентификатором уже существует, обновляем его
                this.account[index] = { ...this.account[index], ...data }
            } else {
                // Добавляем новый объект в массив
                this.account.push(data)
            }
        },

        async fetchOwnerData(accountId, ownerId, taskId = null) {
            this.isOwnerDataLoading = taskId

            // Поиск владельца по ID в массиве ownerData
            const existingOwnerData = this.ownerData.find(owner => owner.id === ownerId)

            // Если данные о владельце уже есть, немедленно возвращаем эти данные
            if (existingOwnerData) {
                return Promise.resolve(existingOwnerData)
                    .finally(() => this.isOwnerDataLoading = null)
            }

            // Если данных о владельце нет, выполняем запросы
            await Promise.all([
                axios.get(`account/data/${ownerId}`),
                axios.get(`account/friends/count/${accountId}/${ownerId}`)
            ])
                .then(responses => {
                    const ownerDataResponse = responses[0]
                    const friendsCountResponse = responses[1]

                    const accountData = ownerDataResponse.data.data.response[0]
                    const friendsCount = friendsCountResponse.data.data.response.count

                    // Объединяем полученные данные
                    const combinedData = { ...accountData, friends_count: friendsCount }

                    // Добавляем новые данные о владельце в массив ownerData
                    this.ownerData.push(combinedData)

                    // Отображаем уведомления об успехе
                    showSuccessNotification(ownerDataResponse.data.message)
                    showSuccessNotification(friendsCountResponse.data.message)

                    return combinedData
                })
                .catch(error => {
                    throw error
                })
                .finally(() => this.isOwnerDataLoading = null)
        },

        async fetchAccountFollowers(accountId) {
            axios.get(`account/followers/${accountId}`)
                .then(response => this.accountFollowers[accountId] = response.data.response.items)
        },

        async fetchAccountFriends(accountId) {
            axios.get(`account/friends/${accountId}`)
                .then(response => this.accountFriends[accountId] = response.data.response.items)
        },

        async fetchAccountFriendsCount(id) {
            const { data } = await axios.get(`account/friends/count/${id}`)
            this.accountFriendsCount = data
        },

        async fetchAccountNewsFeed(accountID, startFrom = null) {
            if (startFrom !== null && startFrom === this.previousNextFrom) return

            this.previousNextFrom = startFrom

            const localAxios = axios
            axiosThrottle.use(localAxios, { requestsPerSecond: 5 })

            localAxios.post('account/newsfeed', {
                account_id: accountID,
                start_from: startFrom
            })
                .then(({ data: { data: { response }, message } }) => {
                    // деструктуризация ответа от сервера для извлечения response и message из data

                    // фильтрация массива items, оставляя только те элементы, у которых первое вложение имеет тип 'photo'
                    const result = response.items.filter(item => item.attachments[0]?.type === 'photo')

                    // сохранение маркера для следующего запроса новостей из response
                    this.nextFrom = response.next_from

                    // если начальный маркер равен null, очистить ленту новостей
                    if (startFrom === null) this.accountNewsFeed = []

                    // добавление отфильтрованных новостей к текущему списку новостей в аккаунте
                    this.accountNewsFeed = [...this.accountNewsFeed, ...result]

                    // установка флага загрузки в false, т.е. загрузка завершена
                    this.isLoadingFeed = false

                    showSuccessNotification(message)
                })
                .catch(() => {
                    this.fetchAccountNewsFeed(accountID, this.nextFrom)
                    // повторный запрос новостей для аккаунта в случае ошибки

                    showErrorNotification('Новые данные ленты не получены')
                })
        },

        async addLike(accountId, ownerId, itemId) {
            await axios.post('account/like', {
                account_id: accountId,
                owner_id: ownerId,
                item_id: itemId
            })
                .then(({ data }) => showSuccessNotification(data.message))
                .catch(error => showErrorNotification(error.response.data.message))
        },

        // TODO: Нигде не используется
        async getScreenNameById(accountId) {
            const { data } = await axios.post('account/get-screen-name-by-id', {
                user_id: accountId
            })

            return data.response
        },

        async addPostsToLike(accountId, taskCount) {
            await axios.post('tasks/get-posts-for-like', {
                account_id: accountId,
                task_count: taskCount
            })
                .then(({ data }) => {
                    this.accountNewsFeed = [...this.accountNewsFeed, ...data.data]
                    showSuccessNotification(data.message)
                })
                .catch(error => showErrorNotification(error))
        },

        async fetchGroupData(groupId) {
            // Поиск владельца по ID в массиве ownerData
            const existingGroupData = this.ownerData.find(owner => owner.id === Math.abs(groupId))
            console.log('existingGroupData', existingGroupData)

            // Если данные о владельце уже есть, немедленно возвращаем эти данные
            if (existingGroupData) return Promise.resolve(existingGroupData)

            const { data } = await axios.get(`group/data/${Math.abs(groupId)}`)
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
            await axios.get(`account/${ownerId}`)
                .then(response => response.data)
                .catch(error => showErrorNotification(error.response.data.message))
        },

        /**
         * Создает задачи на лайки для указанных пользователей
         * @param {number} accountId - ID аккаунта
         * @param {Array} domains - Массив доменов пользователей
         * @returns {Promise}
         */
        async createTasksForUsers(accountId, domains) {
            return axios.post('tasks/create-for-users', {
                account_id: accountId,
                domains
            })
                .then(({ data }) => {
                    showSuccessNotification(data.message)
                    return data.data
                })
                .catch(error => {
                    const message = error.response?.data?.message || 'Произошла ошибка при создании задач'
                    showErrorNotification(message)
                    throw error
                })
        }
    },

    getters: {
        getAccountFollowers: (state) => (accountId) => state.accountFollowers[accountId] || [],
        getAccountFriends: (state) => (accountId) => state.accountFriends[accountId] || [],
        getOwnerDataById: state => (id) => state.ownerData.find(user => user.id === Math.abs(id)),
        getAccountById: state => (id) => state.account.find(account => account.id === Math.abs(id))
    }
})
