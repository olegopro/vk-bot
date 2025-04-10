import { defineStore } from 'pinia'
import axios from '@/helpers/axiosConfig'
import { showErrorNotification, showSuccessNotification } from '@/helpers/notyfHelper'

export const useFilterStore = defineStore('filter', {
    state: () => ({
        cities: [],
        isLoadingCities: false,
        selectedCity: null
    }),

    actions: {
        searchCities(query, countryId = 1, count = 10) {
            // Проверка длины запроса
            if (query.length < 2) return
            this.isLoadingCities = true

            return axios.post('filters/cities', {
                q: query,
                country_id: countryId,
                count
            })
                .then(({ data }) => {
                    this.cities = data.data.items
                    showSuccessNotification(data.message)
                })
                .catch(error => showErrorNotification(error.response.data.message))
                .finally(() => this.isLoadingCities = false)
        },

        /**
         * Получает список пользователей по ID города
         * @param {number} accountId - ID аккаунта
         * @param {number} cityId - ID города
         * @param {number} count - Количество пользователей (опционально)
         * @returns {Promise<Array>} Массив доменов пользователей
         */
        async getUsersByCity(accountId, cityId, count = 10) {
            this.isLoadingUsers = true

            return axios.post('filters/users-by-city', {
                account_id: accountId,
                city_id: cityId,
                count
            })
                .then(({ data }) => {
                    if (data.data.domains) {
                        showSuccessNotification(data.message)
                        return data.data.domains
                    }
                    throw new Error('Не удалось получить список пользователей')
                })
                .catch(error => {
                    const message = error.response?.data?.message || error.message || 'Произошла ошибка при поиске пользователей'
                    showErrorNotification(message)
                    throw error
                })
                .finally(() => this.isLoadingUsers = false)
        },

        selectCity(city) {
            this.selectedCity = city
            this.cities = []
        },

        clearCitySelection() {
            this.selectedCity = null
            this.cities = []
        }
    }
})
