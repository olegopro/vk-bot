<template>
    <div class="modal fade" id="addCyclicTaskModal" tabindex="-1" aria-labelledby="Add task" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <form @submit.prevent="addNewTask" class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="Delete task">Добавление циклической задачи</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <select class="form-select mb-3" v-model="accountId">
                        <option value="selectAccount">Выберите аккаунт</option>
                        <option v-for="account in accountsStore.accounts" :key="account.id" :value="account.account_id">
                            {{ account.screen_name }} ({{ account.first_name }} {{ account.last_name }})
                        </option>
                    </select>

                    <div class="input-group mb-3">
                        <span class="input-group-text">Количество лайков (всего)</span>
                        <input type="number" class="form-control" placeholder="По умолчанию 10 постов" v-model="totalTaskCount">
                    </div>

                    <div class="input-group mb-3">
                        <span class="input-group-text">Количество лайков в час</span>
                        <input type="number" min="1" max="60" class="form-control" placeholder="По умолчанию 10 постов" v-model="tasksPerHour">
                    </div>

                    <select class="form-select mb-3" v-model="status">
                        <option selected value="active">Запустить сейчас</option>
                        <option value="pause">Оставить на паузе</option>
                    </select>

                    <div class="accordion mb-3" id="accordionTimePicker">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTimePicker" aria-expanded="false" aria-controls="collapseTimePicker">
                                    Расписание
                                </button>
                            </h2>
                            <div id="collapseTimePicker" class="accordion-collapse collapse" data-bs-parent="#accordionTimePicker">
                                <div class="accordion-body">
                                    <TimePicker @update:selectedTimes="handleSelectedTimes" />
                                </div>
                            </div>
                        </div>
                    </div><div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="modalHide">Отмена</button>
                    <button type="submit" class="btn btn-success" :disabled="disablePost">
                        Создать
                        <span v-if="loading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    </button>
                </div>
                </div>

            </form>
        </div>
    </div>
</template>

<script setup>
    import { ref, watch, defineProps, onMounted, onUnmounted, inject } from 'vue'
    import { useAccountsStore } from '../../../stores/AccountsStore'
    import { showErrorNotification, showSuccessNotification } from '../../../helpers/notyfHelper'
    import { useCyclicTasksStore } from '../../../stores/CyclicTasksStore'
    import TimePicker from '../TimePicker.vue'

    const props = defineProps({
        // TODO: Если убираю неиспользуемый пропс то при выборе в select модальное окно становиться display: none
        taskId: Number
    })

    const accountsStore = useAccountsStore()
    const cyclicTaskStore = useCyclicTasksStore()

    const accountId = ref('selectAccount')
    const totalTaskCount = ref(1000)
    const tasksPerHour = ref(60)
    const status = ref('active')
    const disablePost = ref(true)
    const loading = ref(false)
    const selectedTimes = ref({})

    const modals = inject('modals')

    watch(accountId, newVal => disablePost.value = newVal === 'selectAccount')

    const addNewTask = () => {
        disablePost.value = true
        loading.value = true

        cyclicTaskStore.createCyclicTask(
            accountId.value,
            tasksPerHour.value,
            totalTaskCount.value,
            status.value,
            selectedTimes.value
        )
            .then(response => {
                modalHide()
                disablePost.value = false
                loading.value = false
                accountId.value = 'selectAccount'
                showSuccessNotification(response)
                cyclicTaskStore.fetchCyclicTasks()
            })
            .catch(error => showErrorNotification(error))
    }

    const handleSelectedTimes = times => selectedTimes.value = times
    const modalHide = () => modals.value.addCyclicTaskModal.hide()

    onMounted(() => console.log('AddCyclicTask onMounted'))
    onUnmounted(() => console.log('AddCyclicTask onUnmounted'))
</script>
