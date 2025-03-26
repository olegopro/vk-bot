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
            if (query.length < 2) return Promise.resolve([])

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
