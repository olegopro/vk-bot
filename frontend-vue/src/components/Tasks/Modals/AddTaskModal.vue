<script setup lang="ts">
  import { ref, getCurrentInstance, computed } from 'vue'
  import { useAccountsStore } from '@/stores/AccountsStore'
  import { useTasksStore } from '@/stores/TasksStore'
  import { showSuccessNotification } from '@/helpers/notyfHelper'
  import { useModal } from '@/composables/useModal'
  import NewsfeedSearch from './AddTaskModal/NewsfeedSearch.vue'
  import CitySearch from './AddTaskModal/CitySearch.vue'
  import FooterSection from '@/global-components/modal-component/footer/FooterSection.vue'
  import type { ApiResponseWrapper } from '@/models/ApiModel'
  import type { VkNewsFeedItem, VkUserFilters } from '@/types/vkontakte'
  import type { CreateTasksResponse } from '@/models/AccountsModel'
  import { useAccountStore } from '@/stores/AccountStore'

  // Рефы для дочерних компонентов
  const newsfeedSearchRef = ref<InstanceType<typeof NewsfeedSearch>>()
  const citySearchRef = ref<InstanceType<typeof CitySearch>>()

  const accountsStore = useAccountsStore()
  const accountStore = useAccountStore()
  const tasksStore = useTasksStore()
  const { closeModal } = useModal()

  const modalId = getCurrentInstance()?.type.__name

  const accountId = ref<string>('selectAccount')
  const taskCount = ref<number>(10)
  const searchType = ref<'newsfeed' | 'city'>('newsfeed')

  // Дефолтные значения для фильтров пользователей
  const DEFAULT_USER_FILTERS: VkUserFilters = {
    sex: null, // 1 - женщина, 2 - мужчина, null - любой
    age_from: null,
    age_to: null,
    online_only: false,
    has_photo: null,
    sort: null, // 0 - по популярности, 1 - по регистрации
    min_friends: null,
    max_friends: null,
    min_followers: null,
    max_followers: null,
    last_seen_days: null,
    is_friend: null
  }

  // Фильтры пользователей для поиска по городу
  const userFilters = ref<VkUserFilters>({ ...DEFAULT_USER_FILTERS })
  const showAdvancedFilters = ref(false)

  // TODO: Нужно проверить точно
  const handleTaskSuccess = (response: ApiResponseWrapper<VkNewsFeedItem[]> | ApiResponseWrapper<CreateTasksResponse>): void => {
    modalHide()
    showSuccessNotification(response.message)
    tasksStore.fetchTasks.execute()
  }

  const modalHide = (): void => {
    closeModal(modalId)
    searchType.value = 'newsfeed'
    showAdvancedFilters.value = false
    // Сброс фильтров
    userFilters.value = { ...DEFAULT_USER_FILTERS }
  }

  const handleTaskSubmit = (): void => {
    switch (searchType.value) {
      case 'newsfeed':
        newsfeedSearchRef.value?.addFeedTask()
        break
      case 'city':
        citySearchRef.value?.addCityTask()
        break
    }
  }

  const loadingStates = {
    newsfeed: computed(() => accountStore.addPostsToLike.loading),
    city: computed(() => accountStore.createTasksForCity.loading)
  }

  const isFooterLoading = computed(() => loadingStates[searchType.value]?.value ?? false)
</script>

<template>
  <div class="modal fade" :id="modalId" tabindex="-1" aria-labelledby="Add task" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="Delete task">Добавление задачи</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

          <!-- Выбор аккаунта и количества постов -->
          <select class="form-select mb-3" aria-label="Default select example" v-model="accountId">
            <option disabled selected value="selectAccount">Выберите аккаунт</option>
            <option v-for="account in accountsStore.fetchAccounts.data" :key="account.account_id" :value="account.account_id">
              {{ account.screen_name }} ({{ account.first_name }} {{ account.last_name }})
            </option>
          </select>

          <div class="input-group mb-3">
            <span class="input-group-text">Количество постов для лайков</span>
            <input type="text" class="form-control" placeholder="По умолчанию 10 постов" v-model="taskCount">
          </div>

          <!-- Переключатели режимов поиска -->
          <div class="form-check form-check-inline mb-3">
            <input class="form-check-input" type="radio" name="searchType" id="newsfeedSearch" value="newsfeed" v-model="searchType">
            <label class="form-check-label" for="newsfeedSearch">Поиск по ленте</label>
          </div>
          <div class="form-check form-check-inline mb-3">
            <input class="form-check-input" type="radio" name="searchType" id="citySearch" value="city" v-model="searchType">
            <label class="form-check-label" for="citySearch">Поиск по городу</label>
          </div>

          <!-- Фильтры для поиска по городу -->
          <div v-if="searchType === 'city'" class="border rounded p-3 mb-3 bg-light">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="mb-0">Фильтры пользователей</h6>
              <button 
                type="button" 
                class="btn btn-sm btn-outline-secondary" 
                @click="showAdvancedFilters = !showAdvancedFilters"
              >
                {{ showAdvancedFilters ? 'Скрыть' : 'Показать' }} расширенные фильтры
              </button>
            </div>

            <!-- Основные фильтры -->
            <div class="row g-3">
              <!-- Пол -->
              <div class="col-md-6">
                <label class="form-label">Пол</label>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="sex" id="sexAny" :value="null" v-model="userFilters.sex">
                  <label class="form-check-label" for="sexAny">Любой</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="sex" id="sexFemale" :value="1" v-model="userFilters.sex">
                  <label class="form-check-label" for="sexFemale">Женщина</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="sex" id="sexMale" :value="2" v-model="userFilters.sex">
                  <label class="form-check-label" for="sexMale">Мужчина</label>
                </div>
              </div>

              <!-- Возраст -->
              <div class="col-md-6">
                <label class="form-label">Возраст</label>
                <div class="row g-2">
                  <div class="col-6">
                    <input 
                      type="number" 
                      class="form-control form-control-sm" 
                      placeholder="От" 
                      min="14" 
                      max="80" 
                      v-model="userFilters.age_from"
                    >
                  </div>
                  <div class="col-6">
                    <input 
                      type="number" 
                      class="form-control form-control-sm" 
                      placeholder="До" 
                      min="14" 
                      max="80" 
                      v-model="userFilters.age_to"
                    >
                  </div>
                </div>
              </div>

              <!-- Только онлайн -->
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="onlineOnly" v-model="userFilters.online_only">
                  <label class="form-check-label" for="onlineOnly">
                    Только онлайн пользователи
                  </label>
                </div>
              </div>

              <!-- Наличие фото -->
              <div class="col-md-6">
                <label class="form-label">Наличие фото</label>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="hasPhoto" id="hasPhotoAny" :value="null" v-model="userFilters.has_photo">
                  <label class="form-check-label" for="hasPhotoAny">Любой</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="hasPhoto" id="hasPhotoYes" :value="true" v-model="userFilters.has_photo">
                  <label class="form-check-label" for="hasPhotoYes">Есть фото</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="hasPhoto" id="hasPhotoNo" :value="false" v-model="userFilters.has_photo">
                  <label class="form-check-label" for="hasPhotoNo">Без фото</label>
                </div>
              </div>

              <!-- Сортировка -->
              <div class="col-md-6">
                <label class="form-label">Сортировка</label>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="sort" id="sortAny" :value="null" v-model="userFilters.sort">
                  <label class="form-check-label" for="sortAny">По умолчанию</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="sort" id="sortPopularity" :value="0" v-model="userFilters.sort">
                  <label class="form-check-label" for="sortPopularity">По популярности</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="sort" id="sortRegistration" :value="1" v-model="userFilters.sort">
                  <label class="form-check-label" for="sortRegistration">По дате регистрации</label>
                </div>
              </div>
            </div>

            <!-- Расширенные фильтры -->
            <div v-if="showAdvancedFilters" class="mt-3 pt-3 border-top">
              <div class="row g-3">
                <!-- Количество друзей -->
                <div class="col-md-6">
                  <label class="form-label">Количество друзей</label>
                  <div class="row g-2">
                    <div class="col-6">
                      <input
                        type="number"
                        class="form-control form-control-sm"
                        placeholder="Мин"
                        min="0"
                        v-model="userFilters.min_friends"
                      >
                    </div>
                    <div class="col-6">
                      <input
                        type="number"
                        class="form-control form-control-sm"
                        placeholder="Макс"
                        min="0"
                        v-model="userFilters.max_friends"
                      >
                    </div>
                  </div>
                </div>

                <!-- Количество подписчиков -->
                <div class="col-md-6">
                  <label class="form-label">Количество подписчиков</label>
                  <div class="row g-2">
                    <div class="col-6">
                      <input
                        type="number"
                        class="form-control form-control-sm"
                        placeholder="Мин"
                        min="0"
                        v-model="userFilters.min_followers"
                      >
                    </div>
                    <div class="col-6">
                      <input
                        type="number"
                        class="form-control form-control-sm"
                        placeholder="Макс"
                        min="0"
                        v-model="userFilters.max_followers"
                      >
                    </div>
                  </div>
                </div>

                <!-- Последняя активность -->
                <div class="col-md-6">
                  <label class="form-label">Последний онлайн (дней назад)</label>
                  <input
                    type="number"
                    class="form-control form-control-sm"
                    placeholder="Максимум дней"
                    min="1"
                    v-model="userFilters.last_seen_days"
                  >
                </div>

                <!-- Есть в друзьях -->
                <div class="col-md-6">
                  <label class="form-label">Статус дружбы</label>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="friendship" id="friendshipAny" :value="null" v-model="userFilters.is_friend">
                    <label class="form-check-label" for="friendshipAny">Любой</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="friendship" id="friendshipTrue" :value="true" v-model="userFilters.is_friend">
                    <label class="form-check-label" for="friendshipTrue">В друзьях</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="friendship" id="friendshipFalse" :value="false" v-model="userFilters.is_friend">
                    <label class="form-check-label" for="friendshipFalse">Не в друзьях</label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Компоненты для разных типов поиска -->
          <NewsfeedSearch
            v-if="searchType === 'newsfeed'"
            ref="newsfeedSearchRef"
            :account-id="accountId"
            :task-count="taskCount"
            @success="handleTaskSuccess"
            @cancel="modalHide"
          />

          <CitySearch
            v-if="searchType === 'city'"
            ref="citySearchRef"
            :account-id="accountId"
            :task-count="taskCount"
            :filters="userFilters"
            @success="handleTaskSuccess"
            @cancel="modalHide"
          />

        </div>

        <FooterSection
          :on-submit="handleTaskSubmit"
          :on-cancel="modalHide"
          :is-loading="isFooterLoading"
        />
      </div>
    </div>
  </div>
</template>
