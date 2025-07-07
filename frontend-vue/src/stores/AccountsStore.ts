import { defineStore } from 'pinia'
import axios from '@/helpers/axiosConfig'
import { showErrorNotification, showSuccessNotification } from '@/helpers/notyfHelper'

interface Account {
    account_id: number
    access_token: string
    screen_name: string
    first_name: string
    last_name: string
    bdate?: string
}

interface AccountsStoreState {
    accounts: Account[]
    isLoading: boolean
}

export const useAccountsStore = defineStore('accounts', {
    state: (): AccountsStoreState => ({
        accounts: [],
        isLoading: false
    }),

    actions: {
        async fetchAccounts() {
            this.isLoading = true
            await axios.get('account/all-accounts')
                .then(({ data }) => {
                    this.accounts = data.data
                    showSuccessNotification(data.message)
                })
                .catch(() => showErrorNotification('Ошибка получения аккаунтов'))
                .finally(() => this.isLoading = false)
        },

        async addAccount(accessToken: string) {
            await axios.post('account/add', { access_token: accessToken })
                .then(({ data }) => {
                    this.accounts.push(data.data)
                    showSuccessNotification(data.message)
                })
                .catch(error => showErrorNotification(error.response.data.message))
        },

        async deleteAccount(accountId: number) {
            await axios.delete(`account/delete-account/${accountId}`)
                .then(({ data }) => {
                    this.accounts = this.accounts.filter(account => account.account_id !== accountId)
                    showSuccessNotification(data.message)
                })
                .catch(({ data }) => showErrorNotification(data.error))
        }
    }
})
