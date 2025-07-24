import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from '@/helpers/axiosConfig'
import useApi from '@/composables/useApi'
import {
  AddAccountResponse,
  AddAccountRequest
} from '@/models/AccountModel'
import { ApiResponseWrapper } from '@/models/ApiModel'

export const useAccountsStore = defineStore('accounts', () => {
  const accounts = ref<AddAccountResponse[]>([])

  /**
   * Получает все аккаунты ВКонтакте
   */
  const fetchAccounts = useApi(async () => {
    return (await axios.get<ApiResponseWrapper<AddAccountResponse[]>>('account/all-accounts')).data
  })

  /**
   * Добавляет новый аккаунт ВКонтакте
   */
  const addAccount = useApi(async (parameters?: AddAccountRequest) => {
    if (!parameters) throw new Error('Не указан токен доступа')
    return (await axios.post<ApiResponseWrapper<AddAccountResponse>>('account/add', { access_token: parameters.access_token })).data
  })

  /**
   * Удаляет аккаунт ВКонтакте
   */
  const deleteAccount = useApi(async (parameters?: { accountId: number }) => {
    if (!parameters) throw new Error('Не указан ID аккаунта')
    return (await axios.delete<ApiResponseWrapper<null>>(`account/delete-account/${parameters.accountId}`)).data
  })

  return {
    accounts,
    fetchAccounts,
    addAccount,
    deleteAccount
  }
})
