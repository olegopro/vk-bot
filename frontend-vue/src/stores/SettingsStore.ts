import { defineStore } from 'pinia'
import axios from '@/helpers/axiosConfig'
import useApi from '@/composables/useApi'
import type {
  SaveSettingsRequest,
  SettingsResponse,
  SaveSettingsResponse
} from '@/models/SettingsModel'
import { ApiResponseWrapper } from '@/models/ApiModel'

export const useSettingsStore = defineStore('settings', () => {
  /**
   * Получает настройки
   */
  const fetchSettings = useApi(async () => {
    return (await axios.get<ApiResponseWrapper<SettingsResponse>>('settings')).data
  })

  /**
   * Сохраняет настройки
   */
  const saveSettings = useApi(async (parameters?: SaveSettingsRequest) => {
    if (!parameters) throw new Error('Не указаны параметры настроек')
    return (await axios.post<ApiResponseWrapper<SaveSettingsResponse>>('settings/save', parameters)).data
  })

  return {
    fetchSettings,
    saveSettings
  }
})
