import { defineStore } from 'pinia'
import axios from '@/helpers/axiosConfig'
import { showErrorNotification, showSuccessNotification } from '@/helpers/notyfHelper'

export const useAccountsStore = defineStore('accounts', {
    state: () => ({
        accounts: [],
        isLoading: false,
        pagination: {}
    }),

    actions: {
        async fetchAccounts(page = 1) {
            this.isLoading = true
            await axios.get(`account/all-accounts?page=${page}`)
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
            await axios.post('account/add', { access_token: accessToken })
                .then(({ data }) => {
                    this.accounts.push(data.data)
                    showSuccessNotification(data.message)
                })
                .catch(error => showErrorNotification(error.response.data.message))
        },

        async deleteAccount(accountId) {
            await axios.delete(`account/delete-account/${accountId}`)
                .then(({ data }) => {
                    this.accounts = this.accounts.filter(account => account.account_id !== accountId)
                    this.pagination.total--
                    showSuccessNotification(data.message)
                })
                .catch(({ data }) => showErrorNotification(data.error))
        }
    }
})
