import { defineStore } from 'pinia'
import axios from 'axios'

export const useAccountsStore = defineStore('accounts', {
    state: () => ({
        accounts: []
    }),

    actions: {
        async fetchAccounts() {
            const { data } = await axios.post('http://localhost:8080/api/accounts')
            this.accounts = data
        },

        async addAccount(accessToken) {
            await axios.post('http://localhost:8080/api/account/add', { access_token: accessToken })
                .then(response => this.accounts.push(response.data))
                .catch(error => console.warn(error))
        },

        async deleteAccount(accountId) {
            await axios.delete(`http://localhost:8080/api/accounts/${accountId}`)
                .then(() => (this.accounts = this.accounts.filter(account => account.account_id !== accountId)))
                .catch(error => console.warn(error))
        }
    },

    getters: {
        getAccounts() {
            return this.accounts
        }
    }
})
