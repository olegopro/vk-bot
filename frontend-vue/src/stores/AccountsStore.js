import { defineStore } from 'pinia'
import axios from 'axios'
import { showErrorNotification, showSuccessNotification } from '../helpers/notyfHelper'

export const useAccountsStore = defineStore('accounts', {
    state: () => ({
        accounts: [],
        isLoading: false,
        pagination: {}
    }),

    actions: {
        async fetchAccounts(page = 1) {
            this.isLoading = true
            await axios.post(`http://localhost:8080/api/account/all-accounts?page=${page}`)
                .then(({ data }) => {
                    page === 1
                        ? this.accounts = data.data
                        : this.accounts = [...this.accounts, ...data.data]

                    this.pagination = data.pagination
                    showSuccessNotification(data.message)
                })
                .catch(() => showErrorNotification('Ошибка получения аккаунтов'))
                .finally(() => this.isLoading = false)
        },

        async addAccount(accessToken) {
            await axios.post('http://localhost:8080/api/account/add', { access_token: accessToken })
                .then(({ data }) => {
                    this.accounts.push(data.data)
                    showSuccessNotification(data.message)
                })
                .catch(({ data }) => showErrorNotification(data.error))
        },

        async deleteAccount(accountId) {
            await axios.delete(`http://localhost:8080/api/account/delete-account/${accountId}`)
                .then(({ data }) => {
                    this.accounts = this.accounts.filter(account => account.account_id !== accountId)
                    showSuccessNotification(data.message)
                })
                .catch(({ data }) => showErrorNotification(data.error))
        }
    }
})
