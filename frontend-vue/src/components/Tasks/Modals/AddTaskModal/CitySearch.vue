<script setup lang="ts">
  import { ref, computed } from 'vue'
  import { useAccountStore } from '@/stores/AccountStore'
  import { useFilterStore } from '@/stores/FilterStore'
  import { showSuccessNotification } from '@/helpers/notyfHelper'
  import { useDebounceFn } from '@vueuse/core'
  import type { VkCity } from '@/types/vkontakte'
  import type { Nullable } from '@/types'
  import type { ApiResponseWrapper } from '@/models/ApiModel'
  import type { CreateTasksResponse } from '@/models/AccountsModel'

  interface CitySearchProps {
    accountId: string
    taskCount: number
  }

  const props = defineProps<CitySearchProps>()

  const emit = defineEmits<{
    success: [response: ApiResponseWrapper<CreateTasksResponse>]
    cancel: []
  }>()

  const accountStore = useAccountStore()
  const filterStore = useFilterStore()

  const cityName = ref<string>('')
  const cityId = ref<number>(0)
  const selectedCity = ref<Nullable<VkCity>>(null)
  const cities = ref<VkCity[]>([])

  const isCitySelected = computed<boolean>(() => cityId.value > 0)

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

  const handleSuccess = (response: ApiResponseWrapper<CreateTasksResponse>): void => {
    // Сброс состояния формы поиска по городу
    cityName.value = ''
    selectedCity.value = null
    cityId.value = 0
    cities.value = []
    emit('success', response)
  }

  const addCityTask = (): void => {
    accountStore.createTasksForCity.execute({
      cityData: {
        account_id: Number(props.accountId),
        city_id: cityId.value,
        count: props.taskCount
      }
    }).then(handleSuccess)
  }
</script>

<template>
  <div>
    <!-- Поиск по городу -->
    <div class="input-group mb-3">
      <span class="input-group-text">Город</span>
      <input
        type="text"
        class="form-control"
        placeholder="Введите название города"
        v-model="cityName"
        @input="handleCityInput"
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
    <div v-if="filterStore.searchCities.loading" class="text-center mb-3">
      <div class="spinner-border spinner-border-sm" role="status">
        <span class="visually-hidden">Загрузка...</span>
      </div>
    </div>

    <!-- Индикатор загрузки при поиске пользователей -->
    <div v-if="accountStore.createTasksForCity.loading" class="text-center mb-3">
      <div class="spinner-border spinner-border-sm" role="status">
        <span class="visually-hidden">Поиск пользователей...</span>
      </div>
    </div>

    <form @submit.prevent="addCityTask">
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" @click="emit('cancel')">Отмена</button>
        <button type="submit" class="btn btn-success" :disabled="accountStore.createTasksForCity.loading || !isCitySelected">
          Создать
          <span v-if="accountStore.createTasksForCity.loading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        </button>
      </div>
    </form>
  </div>
</template>

<style scoped lang="scss">
  .city-results {
    max-height: 200px;
    overflow-y: auto;
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
</style>
