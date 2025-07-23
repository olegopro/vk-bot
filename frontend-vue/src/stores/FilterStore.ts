import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from '@/helpers/axiosConfig'
import { showErrorNotification, showSuccessNotification } from '@/helpers/notyfHelper'
import type {
  City,
  CitySearchResponse,
  UsersByCityResponse
} from '@/models/FilterModel'
import type { Nullable } from '@/types'

export const useFilterStore = defineStore('filter', () => {
  // State
  const cities = ref<City[]>([])
  const isLoadingCities = ref<boolean>(false)
  const isLoadingUsers = ref<boolean>(false)
  const selectedCity = ref<Nullable<City>>(null)

  /**
   * Поиск городов по запросу
   */
  const searchCities = (query: string, countryId = 1, count = 10) => {
    // Проверка длины запроса
    if (query.length < 2) return
    isLoadingCities.value = true

    return axios.post<CitySearchResponse>('filters/cities', {
      q: query,
      country_id: countryId,
      count
    })
      .then(response => {
        cities.value = response.data.data.items
        showSuccessNotification(response.data.message)
        return response.data
      })
      .catch(error => {
        const message = error.response?.data?.message || 'Произошла ошибка при поиске городов'
        showErrorNotification(message)
        throw error
      })
      .finally(() => {
        isLoadingCities.value = false
      })
  }

  /**
   * Получает список пользователей по ID города
   * @param accountId - ID аккаунта
   * @param cityId - ID города
   * @param count - Количество пользователей (опционально)
   * @returns Массив доменов пользователей
   */
  const getUsersByCity = async (accountId: number, cityId: number, count = 10) => {
    isLoadingUsers.value = true

    return axios.post<UsersByCityResponse>('filters/users-by-city', {
      account_id: accountId,
      city_id: cityId,
      count
    })
      .then(response => {
        if (response.data.data.domains) {
          showSuccessNotification(response.data.message)
          return response.data.data.domains
        }
        throw new Error('Не удалось получить список пользователей')
      })
      .catch(error => {
        const message = error.response?.data?.message || error.message || 'Произошла ошибка при поиске пользователей'
        showErrorNotification(message)
        throw error
      })
      .finally(() => {
        isLoadingUsers.value = false
      })
  }

  /**
   * Выбирает город и очищает список результатов поиска
   */
  const selectCity = (city: City): void => {
    selectedCity.value = city
    cities.value = []
  }

  /**
   * Очищает выбранный город и список результатов поиска
   */
  const clearCitySelection = (): void => {
    selectedCity.value = null
    cities.value = []
  }

  return {
    // State
    cities,
    isLoadingCities,
    isLoadingUsers,
    selectedCity,
    
    // Actions
    searchCities,
    getUsersByCity,
    selectCity,
    clearCitySelection
  }
})
