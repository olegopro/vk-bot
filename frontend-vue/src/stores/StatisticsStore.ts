import { defineStore } from 'pinia'
import axios from '@/helpers/axiosConfig'
import useApi from '@/composables/useApi'
import type { WeeklyTaskStatsResponse } from '@/models/StatisticsModel'
import { ApiResponseWrapper } from '@/models/ApiModel'

export const useStatisticsStore = defineStore('statistics', () => {
  // Методы
  const fetchWeeklyTaskStats = useApi(async () => {
    return (await axios.get<ApiResponseWrapper<WeeklyTaskStatsResponse>>('statistics')).data
  })

  return {
    fetchWeeklyTaskStats
  }
})
