<template>
    <div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="Add task" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form @submit.prevent="addNewTask" class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="Delete task">Добавление задачи</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
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

                    <div class="input-group mb-3">
                        <span class="input-group-text">Город</span>
                        <input
                            type="text"
                            class="form-control"
                            placeholder="Введите название города"
                            v-model="cityQuery"
                            @input="handleCityInput"
                        >
                    </div>

                    <!-- Отображение результатов поиска города -->
                    <div v-if="filterStore.cities.length > 0" class="city-results mb-3">
                        <div
                            v-for="city in filterStore.cities"
                            :key="city.id"
                            class="city-item p-2 border-bottom"
                            @click="selectCity(city)"
                        >
                            {{ city.title }}, {{ city.region }}
                        </div>
                    </div>

                    <!-- Индикатор загрузки при поиске городов -->
                    <div v-if="filterStore.isLoadingCities" class="text-center mb-3">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Загрузка...</span>
                        </div>
                    </div>

                    <!-- Отображение выбранного города -->
                    <div v-if="filterStore.selectedCity" class="selected-city mb-3">
                        <span class="badge bg-primary">{{ filterStore.selectedCity.title }}</span>
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
    const cityQuery = ref('')

    const closeModal = inject('closeModal')

    watch(accountId, newVal => disablePost.value = newVal === 'selectAccount')

    // Функция поиска городов с задержкой в 500 мс
    const debouncedSearchCities = useDebounceFn((query) => {
        filterStore.searchCities(query)
    }, 500)

    // Обработчик ввода в поле поиска города
    const handleCityInput = () => {
        if (filterStore.selectedCity) {
            filterStore.clearCitySelection()
        }
        debouncedSearchCities(cityQuery.value)
    }

    // Выбор города из списка
    const selectCity = (city) => {
        filterStore.selectCity(city)
        cityQuery.value = city.title
    }

    const addNewTask = () => {
        disablePost.value = true
        loading.value = true

        // Здесь можно добавить cityId в запрос, если город выбран
        const cityId = filterStore.selectedCity ? filterStore.selectedCity.id : null

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
    }

    const modalHide = () => {
        closeModal('addTaskModal')
        // Сброс состояния при закрытии модального окна
        cityQuery.value = ''
        filterStore.clearCitySelection()
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

            &:last-child {
                border-bottom: none !important;
            }
        }
    }
</style>
