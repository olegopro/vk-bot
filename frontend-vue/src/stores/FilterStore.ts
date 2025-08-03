import { defineStore } from 'pinia'
import axios from '@/helpers/axiosConfig'
import useApi from '@/composables/useApi'
import type {
  CitySearchRequest,
  UsersByCityRequest,
  CitySearchData,
  UsersByCityData
} from '@/models/FilterModel'
import type { ApiResponseWrapper } from '@/models/ApiModel'

export const useFilterStore = defineStore('filter', () => {
  /**
   * Поиск городов по запросу
   */
  const searchCities = useApi(async (parameters?: { citiesData: CitySearchRequest }) => {
    if (!parameters) throw new Error('Не указаны параметры для поиска городов')
    return (await axios.post<ApiResponseWrapper<CitySearchData>>('filters/cities', parameters.citiesData)).data
  })

  /**
   * Получает список пользователей по ID города
   */
  const getUsersByCity = useApi(async (parameters?: { usersData: UsersByCityRequest }) => {
    if (!parameters) throw new Error('Не указаны параметры для поиска пользователей')
    return (await axios.post<ApiResponseWrapper<UsersByCityData>>('filters/users-by-city', parameters.usersData)).data
  })

  return {
    searchCities,
    getUsersByCity
  }
})
