import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from '@/helpers/axiosConfig'
import { showSuccessNotification } from '@/helpers/notyfHelper'
import useApi from '@/composables/useApi'
import {
    Account,
    AccountListResponse,
    AccountResponse,
    DeleteAccountResponse,
    AddAccountRequest
} from '@/models/AccountModel'

export const useAccountsStore = defineStore('accounts', () => {
    const accounts = ref<Account[]>([])

    /**
     * Получает все аккаунты ВКонтакте
     */
    const fetchAccounts = useApi(async () => {
        const response = await axios.get<AccountListResponse>('account/all-accounts')
        accounts.value = response.data.data
        showSuccessNotification(response.data.message)

        return response.data
    })

    /**
     * Добавляет новый аккаунт ВКонтакте
     */
    const addAccount = useApi(async (parameters?: { accessToken: string }) => {
        if (!parameters) throw new Error('Не указан токен доступа')

        const request: AddAccountRequest = { access_token: parameters.accessToken }
        const response = await axios.post<AccountResponse>('account/add', request)

        accounts.value.push(response.data.data)
        showSuccessNotification(response.data.message)

        return response.data
    })

    /**
     * Удаляет аккаунт ВКонтакте
     */
    const deleteAccount = useApi(async (parameters?: { accountId: number }) => {
        if (!parameters) throw new Error('Не указан ID аккаунта')

        const response = await axios.delete<DeleteAccountResponse>(`account/delete-account/${parameters.accountId}`)

        accounts.value = accounts.value.filter(account => account.account_id !== parameters.accountId)
        showSuccessNotification(response.data.message)

        return response.data
    })

    return {
        accounts,
        fetchAccounts,
        addAccount,
        deleteAccount
    }
})
