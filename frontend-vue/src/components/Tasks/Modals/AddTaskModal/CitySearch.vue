<script setup lang="ts">
  import { ref } from 'vue'
  import { useAccountStore } from '@/stores/AccountStore'
  import { useFilterStore } from '@/stores/FilterStore'
  import { showSuccessNotification } from '@/helpers/notyfHelper'
  import { useDebounceFn } from '@vueuse/core'
  import type { VkCity, VkUserFilters } from '@/types/vkontakte'
  import type { Nullable } from '@/types'
  import type { ApiResponseWrapper } from '@/models/ApiModel'
  import type { CreateTasksResponse } from '@/models/AccountsModel'

  interface CitySearchProps {
    accountId: string
    taskCount: number
    filters: VkUserFilters
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

  // const isCitySelected = computed<boolean>(() => cityId.value > 0)

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
    // Создаем объект с данными для создания задач, включая фильтры
    const cityData = {
      account_id: Number(props.accountId),
      city_id: cityId.value,
      count: props.taskCount,
      // Добавляем фильтры только если они не пустые
      ...(props.filters.sex !== null && { sex: props.filters.sex }),
      ...(props.filters.age_from !== null && { age_from: props.filters.age_from }),
      ...(props.filters.age_to !== null && { age_to: props.filters.age_to }),
      ...(props.filters.online_only && { online_only: props.filters.online_only }),
      ...(props.filters.has_photo !== null && { has_photo: props.filters.has_photo }),
      ...(props.filters.sort !== null && { sort: props.filters.sort }),
      ...(props.filters.min_friends !== null && { min_friends: props.filters.min_friends }),
      ...(props.filters.max_friends !== null && { max_friends: props.filters.max_friends }),
      ...(props.filters.min_followers !== null && { min_followers: props.filters.min_followers }),
      ...(props.filters.max_followers !== null && { max_followers: props.filters.max_followers }),
      ...(props.filters.last_seen_days !== null && { last_seen_days: props.filters.last_seen_days }),
      ...(props.filters.is_friend !== null && { is_friend: props.filters.is_friend })
    }

    accountStore.createTasksForCity.execute({
      cityData
    }).then(handleSuccess)
  }

  defineExpose({ addCityTask })
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
    <div v-if="filterStore.searchCities.loading" class="d-flex align-items-center justify-content-center mb-3 p-2 bg-light rounded">
      <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
        <span class="visually-hidden">Загрузка...</span>
      </div>
      <small class="text-muted">Поиск городов...</small>
    </div>

    <!-- Индикатор загрузки при создании задач -->
    <div v-if="accountStore.createTasksForCity.loading" class="d-flex align-items-center justify-content-center p-3 bg-success bg-opacity-10 border border-success border-opacity-25 rounded">
      <div class="spinner-grow spinner-grow-sm text-success me-2" role="status">
        <span class="visually-hidden">Создание задач...</span>
      </div>
      <small class="text-success fw-medium">Создание задач для пользователей из города...</small>
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
</style>
