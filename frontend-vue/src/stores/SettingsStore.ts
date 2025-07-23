import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from '@/helpers/axiosConfig'
import { showSuccessNotification } from '@/helpers/notyfHelper'
import useApi from '@/composables/useApi'
import type {
  Settings,
  SaveSettingsRequest,
  SettingsResponse,
  SaveSettingsResponse
} from '@/models/SettingsModel'

export const useSettingsStore = defineStore('settings', () => {
  // Состояние
  const settings = ref<Settings>({
    show_followers: false,
    show_friends: false,
    task_timeout: 0
  })

  /**
   * Получает настройки
   */
  const fetchSettings = useApi(async () => {
    const response = await axios.get<SettingsResponse>('settings')
    
    if (response.data.data && response.data.data.length > 0) {
      settings.value = response.data.data[0]
    }
    
    showSuccessNotification(response.data.message)
    return response.data
  })

  /**
   * Сохраняет настройки
   */
  const saveSettings = useApi(async (parameters?: SaveSettingsRequest) => {
    if (!parameters) throw new Error('Не указаны параметры настроек')

    const { showFollowers, showFriends, taskTimeout } = parameters

    const response = await axios.post<SaveSettingsResponse>('settings/save', {
      show_followers: showFollowers,
      show_friends: showFriends,
      task_timeout: taskTimeout
    })

    // Обновляем локальное состояние
    settings.value = {
      ...settings.value,
      show_followers: showFollowers,
      show_friends: showFriends,
      task_timeout: taskTimeout
    }

    showSuccessNotification(response.data.message)
    return response.data
  })

  return {
    // Состояние
    settings,
    
    // Методы
    fetchSettings,
    saveSettings
  }
})