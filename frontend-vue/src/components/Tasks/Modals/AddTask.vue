<template>

    <div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="Add task" aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">
            <form @submit.prevent="addNewTask" class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="Delete task">Добавление задачи</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <!-- Выбор аккаунта и количества постов -->
                    <select class="form-select mb-3" aria-label="Default select example" v-model="accountId">
                        <option disabled selected value="selectAccount">Выберите аккаунт</option>
                        <option v-for="account in accountsStore.accounts" :key="account.id" :value="account.account_id">
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

                    <!-- Поиск по городу (отображается только при выборе соответствующего режима) -->
                    <div v-if="searchType === 'city'">
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
                        <div v-if="filterStore.cities.length > 0" class="city-results mb-3">
                            <div
                                v-for="city in filterStore.cities"
                                :key="city.id"
                                class="city-item p-2 border-bottom"
                                :class="{ 'selected-city': selectedCity?.id === city.id }"
                                @click="selectCity(city)"
                            >
                                {{ city.title }} <span v-if="city.region">, {{ city.region }}</span>
                            </div>
                        </div>

                        <!-- Индикатор загрузки при поиске городов -->
                        <div v-if="filterStore.isLoadingCities" class="text-center mb-3">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Загрузка...</span>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="modalHide">Отмена</button>
                    <button type="submit" class="btn btn-success" :disabled="disablePost">
                        Создать
                        <span v-if="loading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
    import { ref, watch, inject, onMounted, onUnmounted } from 'vue'
    import { useAccountStore } from '@/stores/AccountStore'
    import { useAccountsStore } from '../../../stores/AccountsStore'
    import { showErrorNotification, showSuccessNotification } from '../../../helpers/notyfHelper'
    import { useTasksStore } from '../../../stores/TasksStore'
    import { useFilterStore } from '../../../stores/FilterStore'
    import { useDebounceFn } from '@vueuse/core'

    const accountStore = useAccountStore()
    const accountsStore = useAccountsStore()
    const tasksStore = useTasksStore()
    const filterStore = useFilterStore()

    const accountId = ref('selectAccount')
    const disablePost = ref(true)
    const loading = ref(false)
    const taskCount = ref(10)
    const cityName = ref('')
    const cityId = ref(0)
    const searchType = ref('newsfeed') // По умолчанию - поиск по ленте
    const selectedCity = ref('')

    const closeModal = inject('closeModal')

    watch(accountId, newVal => disablePost.value = newVal === 'selectAccount')

    // Функция поиска городов с задержкой в 500 мс
    const debouncedSearchCities = useDebounceFn((query) => {
        filterStore.searchCities(query)
    }, 500)

    // Обработчик ввода в поле поиска города
    const handleCityInput = () => {
        selectedCity.value = null
        cityId.value = 0
        debouncedSearchCities(cityName.value)
    }

    // Выбор города из списка
    const selectCity = city => {
        selectedCity.value = city
        cityName.value = city.title
        cityId.value = city.id
    }

    const addNewTask = () => {
        disablePost.value = true
        loading.value = true

        if (searchType.value === 'newsfeed') {
            // Существующая логика поиска по ленте
            accountStore.addPostsToLike(accountId.value, taskCount.value)
                .then(response => {
                    modalHide()
                    disablePost.value = false
                    loading.value = false
                    accountId.value = 'selectAccount'
                    showSuccessNotification(response)
                    tasksStore.fetchTasks()
                })
                .catch(error => showErrorNotification(error))
        } else {
            // Новая логика поиска по городу
            if (!cityId.value) {
                showErrorNotification('Необходимо выбрать город')
                disablePost.value = false
                loading.value = false
                return
            }

            // Получаем список пользователей по городу
            filterStore.getUsersByCity(accountId.value, cityId.value, taskCount.value)
                .then(domains => {
                    // Создаем задачи на лайки для найденных пользователей
                    if (domains && domains.length > 0) {
                        return accountStore.createTasksForUsers(accountId.value, domains)
                    } else {
                        throw new Error('Не удалось найти пользователей в выбранном городе')
                    }
                })
                .then(() => {
                    modalHide()
                    disablePost.value = false
                    loading.value = false
                    accountId.value = 'selectAccount'
                    showSuccessNotification('Задачи на лайки успешно созданы')
                    tasksStore.fetchTasks()
                })
                .catch(error => {
                    showErrorNotification(error.message || 'Произошла ошибка при создании задач')
                    disablePost.value = false
                    loading.value = false
                })
        }
    }

    const modalHide = () => {
        closeModal('addTaskModal')
        // Сброс состояния при закрытии модального окна
        cityName.value = ''
        selectedCity.value = null
        cityId.value = 0
        searchType.value = 'newsfeed'
    }

    onMounted(() => console.log('AddTaskModal onMounted'))
    onUnmounted(() => console.log('AddTaskModal onUnmounted'))
</script>

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
