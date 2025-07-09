import { defineStore } from 'pinia'
import axios from '@/helpers/axiosConfig'
import { showErrorNotification, showSuccessNotification } from '@/helpers/notyfHelper'

export const useStatisticsStore = defineStore('statistics', {
  state: () => ({
    weeklyTaskStats: {}
  }),

  actions: {
    fetchWeeklyTaskStats() {
      axios.get('statistics')
        .then(({ data }) => {
          this.weeklyTaskStats = data.data
          showSuccessNotification(data.message)
        })
        .catch(() => showErrorNotification('Ошибка получения статистики'))
    }
  }
})
