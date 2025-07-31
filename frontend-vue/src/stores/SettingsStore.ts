import { defineStore } from 'pinia'
import axios from '@/helpers/axiosConfig'
import useApi from '@/composables/useApi'
import type {
  SaveSettingsRequest,
  SettingsResponse,
  SaveSettingsResponse
} from '@/models/SettingsModel'

export const useSettingsStore = defineStore('settings', () => {
  /**
   * Получает настройки
   */
  const fetchSettings = useApi(async () => {
    return (await axios.get<SettingsResponse>('settings')).data
  })

  /**
   * Сохраняет настройки
   */
  const saveSettings = useApi(async (parameters?: SaveSettingsRequest) => {
    if (!parameters) throw new Error('Не указаны параметры настроек')
    return (await axios.post<SaveSettingsResponse>('settings/save', parameters)).data
  })

  return {
    fetchSettings,
    saveSettings
  }
})
