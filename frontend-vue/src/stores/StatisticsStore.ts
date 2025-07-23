import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from '@/helpers/axiosConfig'
import { showSuccessNotification } from '@/helpers/notyfHelper'
import useApi from '@/composables/useApi'
import type { WeeklyTaskStats, StatisticsResponse } from '@/models/StatisticsModel'

export const useStatisticsStore = defineStore('statistics', () => {
  // Состояние
  const weeklyTaskStats = ref<WeeklyTaskStats>({})

  // Методы
  const fetchWeeklyTaskStats = useApi(async () => {
    const response = await axios.get<StatisticsResponse>('statistics')

    weeklyTaskStats.value = response.data.data
    showSuccessNotification(response.data.message)

    return response.data
  })

  return {
    // Состояние
    weeklyTaskStats,

    // Методы
    fetchWeeklyTaskStats
  }
})
