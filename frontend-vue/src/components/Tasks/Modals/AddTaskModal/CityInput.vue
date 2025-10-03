<script setup lang="ts">
  import { ref } from 'vue'
  import { useFilterStore } from '@/stores/FilterStore'
  import { useAccountStore } from '@/stores/AccountStore'
  import { showSuccessNotification } from '@/helpers/notyfHelper'
  import { filterNullableValues } from '@/helpers/objectHelper'
  import { useDebounceFn } from '@vueuse/core'
  import type { VkCity, VkUserFilters } from '@/types/vkontakte'
  import type { Nullable } from '@/types'
  import type { ApiResponseWrapper } from '@/models/ApiModel'
  import type { CreateTasksResponse } from '@/models/AccountsModel'

  interface CityInputProps {
    accountId: string
    taskCount: number
    filters: VkUserFilters
  }

  const props = defineProps<CityInputProps>()

  const emit = defineEmits<{
    success: [response: ApiResponseWrapper<CreateTasksResponse>]
  }>()

  const filterStore = useFilterStore()
  const accountStore = useAccountStore()

  const cityName = ref<string>('')
  const cityId = ref<number>(0)
  const selectedCity = ref<Nullable<VkCity>>(null)
  const cities = ref<VkCity[]>([])

  // Состояние фокуса для input поля города
  const isCityInputFocused = ref<boolean>(false)

  // Функция поиска городов с задержкой в 500 мс
  const debouncedSearchCities = useDebounceFn((query: string) => {
    if (query.length < 2) {
      cities.value = []
      return
    }

    filterStore.searchCities.execute({
      citiesData: {
        q: query,
        country_id: 1,
        count: 10
      }
    })
      .then(response => {
        cities.value = response.data.items
        showSuccessNotification(response.message)
      })
  }, 500)

  // Обработчик ввода в поле поиска города
  const handleCityInput = (): void => {
    selectedCity.value = null
    cityId.value = 0
    debouncedSearchCities(cityName.value)
  }

  // Выбор города из списка
  const handleCitySelection = (city: VkCity): void => {
    selectedCity.value = city
    cityName.value = city.title
    cityId.value = city.id
    cities.value = []
  }

  // Метод для сброса состояния (вызывается из родителя)
  const reset = (): void => {
    cityName.value = ''
    selectedCity.value = null
    cityId.value = 0
    cities.value = []
  }

  // Создание задач для города
  const handleSuccess = (response: ApiResponseWrapper<CreateTasksResponse>): void => {
    emit('success', response)
  }

  const addCityTask = (): void => {
    const cityData = {
      account_id: Number(props.accountId),
      city_id: cityId.value,
      count: props.taskCount,
      ...filterNullableValues(props.filters)
    }

    accountStore.createTasksForCity.execute({
      cityData
    }).then(handleSuccess)
  }

  defineExpose({ reset, addCityTask })
</script>

<template>
  <div>
    <!-- Поиск по городу -->
    <div class="input-group mb-3" style="box-shadow: rgba(0, 0, 0, 0.1) 0px 0px 10px inset; border-radius: 0.375rem; border: 1px solid #49b4f082;">
      <span class="input-group-text" style="background-color: #f8f9fa; border: none;">Город</span>
      <input
        type="text"
        class="form-control"
        :class="{ 'focused-input': isCityInputFocused }"
        placeholder="Введите название города"
        v-model="cityName"
        @input="handleCityInput"
        @focus="isCityInputFocused = true"
        @blur="isCityInputFocused = false"
        style="border: none; font-size: 1.1rem; padding: 0.75rem 1rem;"
      >
    </div>

    <!-- Отображение результатов поиска города -->
    <div v-if="cities.length > 0" class="city-results mb-3">
      <div
        v-for="city in cities"
        :key="city.id"
        class="city-item p-2 border-bottom"
        :class="{ 'selected-city': selectedCity?.id === city.id }"
        @click="handleCitySelection(city)"
      >
        {{ city.title }} <span v-if="city.region">, {{ city.region }}</span>
      </div>
    </div>

    <!-- Индикатор загрузки при поиске городов -->
    <div v-if="filterStore.searchCities.loading" class="d-flex align-items-center justify-content-center mb-3 p-2 bg-light rounded">
      <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
        <span class="visually-hidden">Загрузка...</span>
      </div>
      <small class="text-muted">Поиск городов...</small>
    </div>
  </div>
</template>

<style scoped lang="scss">
  .city-results {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;

    .city-item {
      cursor: pointer;

      &:hover {
        background-color: #f8f9fa;
      }

      &.selected-city {
        background-color: #e7f1ff;
      }

      &:last-child {
        border-bottom: none !important;
      }
    }
  }

  .focused-input {
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25), 0 4px 12px rgba(0, 123, 255, 0.3) !important;
    transition: box-shadow 0.3s ease;
  }
</style>
