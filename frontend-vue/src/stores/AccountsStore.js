import { defineStore } from 'pinia'
import axios from 'axios'
import { showErrorNotification, showSuccessNotification } from '../helpers/notyfHelper'

export const useAccountsStore = defineStore('accounts', {
	state: () => ({
		accounts: []
	}),

	actions: {
		async fetchAccounts() {
			await axios.post('http://localhost:8080/api/account/all-accounts')
				.then(({ data }) => {
					this.accounts = data.data
					showSuccessNotification(data.message)
				})
				.catch(() => showErrorNotification('Ошибка получения аккаунтов'))
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
