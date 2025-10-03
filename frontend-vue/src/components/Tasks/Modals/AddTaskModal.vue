<script setup lang="ts">
  import { ref, getCurrentInstance, computed } from 'vue'
  import { useAccountsStore } from '@/stores/AccountsStore'
  import { useTasksStore } from '@/stores/TasksStore'
  import { showSuccessNotification } from '@/helpers/notyfHelper'
  import { useModal } from '@/composables/useModal'
  import NewsfeedSearch from '@/components/Tasks/Modals/AddTaskModal/NewsfeedSearch.vue'
  import CityInput from '@/components/Tasks/Modals/AddTaskModal/CityInput.vue'
  import FooterSection from '@/components-ui/modal-component/sections/FooterSection.vue'
  import BodySection from '@/components-ui/modal-component/sections/BodySection.vue'
  import type { ApiResponseWrapper } from '@/models/ApiModel'
  import type { VkNewsFeedItem, VkUserFilters } from '@/types/vkontakte'
  import type { CreateTasksResponse } from '@/models/AccountsModel'
  import { useAccountStore } from '@/stores/AccountStore'

  // Рефы для дочерних компонентов
  const newsfeedSearchRef = ref<InstanceType<typeof NewsfeedSearch>>()
  const cityInputRef = ref<InstanceType<typeof CityInput>>()

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

  // Состояние свернутых карточек фильтров
  const collapsedCards = ref<Record<string, boolean>>({
    gender: true,
    age: true,
    photo: true,
    sort: true,
    friends: true,
    followers: true,
    activity: true
  })

  // Состояние свернутого блока фильтров
  const isFiltersCollapsed = ref<boolean>(true)

  // Переключение состояния свернутого блока фильтров
  const toggleFiltersCollapse = (): void => {
    isFiltersCollapsed.value = !isFiltersCollapsed.value
  }

  // Переключение состояния карточки
  const toggleCardCollapse = (cardKey: string): void => {
    collapsedCards.value[cardKey] = !collapsedCards.value[cardKey]
  }

  // TODO: Нужно проверить точно
  const handleTaskSuccess = (response: ApiResponseWrapper<VkNewsFeedItem[]> | ApiResponseWrapper<CreateTasksResponse>): void => {
    modalHide()
    showSuccessNotification(response.message)
    tasksStore.fetchTasks.execute()
  }

  const modalHide = (): void => {
    closeModal(modalId)
    searchType.value = 'newsfeed'
    // Сброс фильтров
    userFilters.value = { ...DEFAULT_USER_FILTERS }
    // Сброс поиска города
    cityInputRef.value?.reset()
  }

  const handleTaskSubmit = (): void => {
    switch (searchType.value) {
      case 'newsfeed':
        newsfeedSearchRef.value?.addFeedTask()
        break
      case 'city':
        cityInputRef.value?.addCityTask()
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

        <BodySection :key="searchType">
          <!-- Выбор аккаунта и количества постов -->
          <select class="form-select mb-3" aria-label="Default select example" v-model="accountId">
            <option disabled selected value="selectAccount">Выберите аккаунт</option>
            <option v-for="account in accountsStore.fetchAccounts.data" :key="account.account_id" :value="account.account_id">
              {{ account.screen_name }} ({{ account.first_name }} {{ account.last_name }})
            </option>
          </select>

          <div class="mb-3">
            <label class="form-label fw-medium">
              Количество постов для лайков
            </label>
            <input
              type="number"
              class="form-control"
              placeholder="По умолчанию 10 постов"
              min="1"
              max="100"
              step="1"
              v-model.number="taskCount"
            >
            <div class="form-text">
              <small class="text-muted">Диапазон: 1-100 постов</small>
            </div>
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

          <!-- Поиск города для режима "Поиск по городу" -->
          <CityInput
            v-if="searchType === 'city'"
            ref="cityInputRef"
            :account-id="accountId"
            :task-count="taskCount"
            :filters="userFilters"
            @success="handleTaskSuccess"
          />

          <!-- Фильтры для поиска по городу -->
          <div v-if="searchType === 'city'" class="border rounded p-0 mb-3 bg-light">
            <h6 class="m-0 p-3 d-flex justify-content-between align-items-center" style="cursor: pointer;" @click="toggleFiltersCollapse">
              <span>Фильтры пользователей</span>
              <i class="bi" :class="{ 'bi-chevron-down': isFiltersCollapsed, 'bi-chevron-up': !isFiltersCollapsed }"></i>
            </h6>

            <!-- Все фильтры -->
            <div v-if="!isFiltersCollapsed">
              <!-- Основные фильтры -->
              <div class="row g-3 p-3">
                <!-- Пол -->
                <div class="col-12">
                  <div class="card border-0 bg-white shadow-sm">
                    <div 
                      class="card-body m-0 p-0"
                    >
                      <h6 class="card-title m-0 p-3 text-primary d-flex justify-content-between align-items-center" style="cursor: pointer;" @click="toggleCardCollapse('gender')">
                        <span><i class="bi bi-person-fill me-2"></i>Пол</span>
                        <i class="bi small-icon" :class="{ 'bi-chevron-down': collapsedCards.gender, 'bi-chevron-up': !collapsedCards.gender }"></i>
                      </h6>
                      <div v-if="!collapsedCards.gender" class="p-3 pt-0" @click.stop>
                        <div class="form-check mb-2">
                          <input class="form-check-input" type="radio" name="sex" id="sexAny" :value="null" v-model="userFilters.sex">
                          <label class="form-check-label" style="cursor: pointer;" for="sexAny">Любой</label>
                        </div>
                        <div class="form-check mb-2">
                          <input class="form-check-input" type="radio" name="sex" id="sexFemale" :value="1" v-model="userFilters.sex">
                          <label class="form-check-label" style="cursor: pointer;" for="sexFemale">Женщина</label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="sex" id="sexMale" :value="2" v-model="userFilters.sex">
                          <label class="form-check-label" style="cursor: pointer;" for="sexMale">Мужчина</label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Возраст -->
                <div class="col-12">
                  <div class="card border-0 bg-white shadow-sm">
                    <div 
                      class="card-body m-0 p-0"
                    >
                      <h6 class="card-title m-0 p-3 text-primary d-flex justify-content-between align-items-center" style="cursor: pointer;" @click="toggleCardCollapse('age')">
                        <span><i class="bi bi-calendar-range me-2"></i>Возраст</span>
                        <i class="bi small-icon" :class="{ 'bi-chevron-down': collapsedCards.age, 'bi-chevron-up': !collapsedCards.age }"></i>
                      </h6>
                      <div v-if="!collapsedCards.age" class="p-3 pt-0" @click.stop>
                        <div class="input-group input-group-sm w-100">
                          <span class="input-group-text bg-light border-end-0">От</span>
                          <input
                            type="number"
                            class="form-control border-start-0 border-end-0 rounded-0"
                            placeholder="14"
                            min="14"
                            max="80"
                            step="1"
                            v-model.number="userFilters.age_from"
                          >
                          <span class="input-group-text bg-light border-start-0 border-end-0">До</span>
                          <input
                            type="number"
                            class="form-control border-start-0 rounded-0"
                            placeholder="80"
                            min="14"
                            max="80"
                            step="1"
                            v-model.number="userFilters.age_to"
                          >
                        </div>
                        <div class="form-text mt-2">
                          <small class="text-muted">Диапазон: 14-80 лет</small>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Наличие фото -->
                <div class="col-12">
                  <div class="card border-0 bg-white shadow-sm">
                    <div 
                      class="card-body m-0 p-0"
                    >
                      <h6 class="card-title m-0 p-3 text-primary d-flex justify-content-between align-items-center" style="cursor: pointer;" @click="toggleCardCollapse('photo')">
                        <span><i class="bi bi-image me-2"></i>Наличие фото</span>
                        <i class="bi small-icon" :class="{ 'bi-chevron-down': collapsedCards.photo, 'bi-chevron-up': !collapsedCards.photo }"></i>
                      </h6>
                      <div v-if="!collapsedCards.photo" class="p-3 pt-0" @click.stop>
                        <div class="form-check mb-2">
                          <input class="form-check-input" type="radio" name="hasPhoto" id="hasPhotoAny" :value="null" v-model="userFilters.has_photo">
                          <label class="form-check-label" style="cursor: pointer;" for="hasPhotoAny">Любой</label>
                        </div>
                        <div class="form-check mb-2">
                          <input class="form-check-input" type="radio" name="hasPhoto" id="hasPhotoYes" :value="true" v-model="userFilters.has_photo">
                          <label class="form-check-label" style="cursor: pointer;" for="hasPhotoYes">Есть фото</label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="hasPhoto" id="hasPhotoNo" :value="false" v-model="userFilters.has_photo">
                          <label class="form-check-label" style="cursor: pointer;" for="hasPhotoNo">Без фото</label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Сортировка -->
                <div class="col-12">
                  <div class="card border-0 bg-white shadow-sm">
                    <div 
                      class="card-body m-0 p-0"
                    >
                      <h6 class="card-title m-0 p-3 text-primary d-flex justify-content-between align-items-center" style="cursor: pointer;" @click="toggleCardCollapse('sort')">
                        <span><i class="bi bi-sort-down me-2"></i>Сортировка</span>
                        <i class="bi small-icon" :class="{ 'bi-chevron-down': collapsedCards.sort, 'bi-chevron-up': !collapsedCards.sort }"></i>
                      </h6>
                      <div v-if="!collapsedCards.sort" class="p-3 pt-0" @click.stop>
                        <div class="form-check mb-2">
                          <input class="form-check-input" type="radio" name="sort" id="sortAny" :value="null" v-model="userFilters.sort">
                          <label class="form-check-label" style="cursor: pointer;" for="sortAny">По умолчанию</label>
                        </div>
                        <div class="form-check mb-2">
                          <input class="form-check-input" type="radio" name="sort" id="sortPopularity" :value="0" v-model="userFilters.sort">
                          <label class="form-check-label" style="cursor: pointer;" for="sortPopularity">По популярности</label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="sort" id="sortRegistration" :value="1" v-model="userFilters.sort">
                          <label class="form-check-label" style="cursor: pointer;" for="sortRegistration">По дате регистрации</label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Социальные связи -->
              <div class="col-12 px-3 py-3">
                <div class="card border-0 bg-info bg-opacity-10">
                  <div 
                    class="card-body m-0 p-0"
                  >
                    <h6 class="card-title m-0 p-3 text-info d-flex justify-content-between align-items-center" style="cursor: pointer;" @click="toggleCardCollapse('friends')">
                      <span><i class="bi bi-people-fill me-2"></i>Социальные связи</span>
                      <i class="bi small-icon" :class="{ 'bi-chevron-down': collapsedCards.friends, 'bi-chevron-up': !collapsedCards.friends }"></i>
                    </h6>
                    <div v-if="!collapsedCards.friends" class="p-3 pt-0" @click.stop>
                      <div class="card border-0 bg-white shadow-sm">
                        <div class="card-body p-0">
                          <div class="row g-3 p-3">
                            <!-- Количество друзей -->
                            <div class="col-12">
                              <label class="form-label fw-medium">
                                <i class="bi bi-person-hearts me-1"></i>Количество друзей
                              </label>
                              <div class="input-group input-group-sm w-100">
                                <span class="input-group-text bg-light border-end-0">От</span>
                                <input
                                  type="number"
                                  class="form-control border-start-0 border-end-0 rounded-0"
                                  placeholder="0"
                                  min="0"
                                  step="1"
                                  v-model.number="userFilters.min_friends"
                                >
                                <span class="input-group-text bg-light border-start-0 border-end-0">До</span>
                                <input
                                  type="number"
                                  class="form-control border-start-0 rounded-0"
                                  placeholder="∞"
                                  min="0"
                                  step="1"
                                  v-model.number="userFilters.max_friends"
                                >
                              </div>
                              <div class="form-text mt-1">
                                <small class="text-muted">Минимальное значение: 0</small>
                              </div>
                            </div>

                            <!-- Количество подписчиков -->
                            <div class="col-12">
                              <label class="form-label fw-medium">
                                <i class="bi bi-person-plus-fill me-1"></i>Количество подписчиков
                              </label>
                              <div class="input-group input-group-sm w-100">
                                <span class="input-group-text bg-light border-end-0">От</span>
                                <input
                                  type="number"
                                  class="form-control border-start-0 border-end-0 rounded-0"
                                  placeholder="0"
                                  min="0"
                                  step="1"
                                  v-model.number="userFilters.min_followers"
                                >
                                <span class="input-group-text bg-light border-start-0 border-end-0">До</span>
                                <input
                                  type="number"
                                  class="form-control border-start-0 rounded-0"
                                  placeholder="∞"
                                  min="0"
                                  step="1"
                                  v-model.number="userFilters.max_followers"
                                >
                              </div>
                              <div class="form-text mt-1">
                                <small class="text-muted">Минимальное значение: 0</small>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Активность и статус -->
              <div class="col-12 px-3">
                <div class="card border-0 bg-warning bg-opacity-10">
                  <div 
                    class="card-body m-0 p-0"
                  >
                    <h6 class="card-title m-0 p-3 text-warning-emphasis d-flex justify-content-between align-items-center" style="cursor: pointer;" @click="toggleCardCollapse('activity')">
                      <span><i class="bi bi-activity me-2"></i>Активность и статус</span>
                      <i class="bi small-icon" :class="{ 'bi-chevron-down': collapsedCards.activity, 'bi-chevron-up': !collapsedCards.activity }"></i>
                    </h6>
                    <div v-if="!collapsedCards.activity" class="p-3 pt-0" @click.stop>
                      <div class="card border-0 bg-white shadow-sm">
                        <div class="card-body p-0">
                          <div class="row g-3 p-3">
                            <!-- Последняя активность -->
                            <div class="col-12">
                              <label class="form-label fw-medium">
                                <i class="bi bi-clock-history me-1"></i>Последний онлайн (дней назад)
                              </label>
                              <div class="input-group input-group-sm w-100">
                                <span class="input-group-text bg-light border-end-0">Макс</span>
                                <input
                                  type="number"
                                  class="form-control border-start-0 border-end-0 rounded-0"
                                  placeholder="30"
                                  min="1"
                                  max="365"
                                  step="1"
                                  v-model.number="userFilters.last_seen_days"
                                >
                                <span class="input-group-text bg-light border-start-0">дней</span>
                              </div>
                              <div class="form-text mt-1">
                                <small class="text-muted">Диапазон: 1-365 дней</small>
                              </div>
                            </div>

                            <!-- Статус дружбы -->
                            <div class="col-12">
                              <label class="form-label fw-medium">
                                <i class="bi bi-heart-fill me-1"></i>Статус дружбы
                              </label>
                              <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="friendship" id="friendshipAny" :value="null" v-model="userFilters.is_friend">
                                <label class="btn btn-outline-secondary btn-sm" for="friendshipAny">Любой</label>

                                <input type="radio" class="btn-check" name="friendship" id="friendshipTrue" :value="true" v-model="userFilters.is_friend">
                                <label class="btn btn-outline-success btn-sm" for="friendshipTrue">В друзьях</label>

                                <input type="radio" class="btn-check" name="friendship" id="friendshipFalse" :value="false" v-model="userFilters.is_friend">
                                <label class="btn btn-outline-danger btn-sm" for="friendshipFalse">Не в друзьях</label>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Только онлайн -->
              <div class="row g-3 my-1 p-3">
                <div class="col-12">
                  <div class="card border-0 bg-primary bg-opacity-10 border-primary border-opacity-25">
                    <div class="card-body p-3">
                      <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="onlineOnly" v-model="userFilters.online_only">
                        <label class="form-check-label fw-medium" style="cursor: pointer;" for="onlineOnly">
                          <i class="bi bi-circle-fill text-success me-2"></i>
                          Только онлайн пользователи
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Индикатор загрузки при создании задач -->
          <div v-if="searchType === 'city' && accountStore.createTasksForCity.loading" class="d-flex align-items-center justify-content-center p-3 bg-success bg-opacity-10 border border-success border-opacity-25 rounded">
            <div class="spinner-grow spinner-grow-sm text-success me-2" role="status">
              <span class="visually-hidden">Создание задач...</span>
            </div>
            <small class="text-success fw-medium">Создание задач для пользователей из города...</small>
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

        </BodySection>

        <FooterSection
          :on-submit="handleTaskSubmit"
          :on-cancel="modalHide"
          :is-loading="isFooterLoading"
        />
      </div>
    </div>
  </div>
</template>
