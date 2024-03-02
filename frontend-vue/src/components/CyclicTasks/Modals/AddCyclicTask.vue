<template>
    <div class="modal fade" id="addCyclicTaskModal" tabindex="-1" aria-labelledby="Add task" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form @submit.prevent="addNewTask" class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="Delete task">Добавление циклической задачи</h1>
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
                        <span class="input-group-text">Количество лайков (всего)</span>
                        <input type="number" class="form-control" placeholder="По умолчанию 10 постов" v-model="totalTaskCount">
                    </div>

                    <div class="input-group mb-3">
                        <span class="input-group-text">Количество лайков в час</span>
                        <input type="number" min="1" max="60" class="form-control" placeholder="По умолчанию 10 постов" v-model="tasksPerHour">
                    </div>

                    <select class="form-select mb-3" aria-label="Default select example" v-model="status">
                        <option selected value="active">Запустить сейчас</option>
                        <option  value="pause">Оставить на паузе</option>

                    </select>

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
    import { ref, watch, defineProps } from 'vue'
    import { useAccountsStore } from '../../../stores/AccountsStore'
    import { showErrorNotification, showSuccessNotification } from '../../../helpers/notyfHelper'
    import { useCyclicTasksStore } from '../../../stores/CyclicTasksStore'

    const props = defineProps({
        modalInstance: Object
    })

    const accountsStore = useAccountsStore()
    const cyclicTaskStore = useCyclicTasksStore()

    const accountId = ref('selectAccount')
    const totalTaskCount = ref(1000)
    const tasksPerHour = ref(60)
    const status = ref('active')
    const disablePost = ref(true)
    const loading = ref(false)

    watch(accountId, newVal => disablePost.value = newVal === 'selectAccount')

    const addNewTask = () => {
        disablePost.value = true
        loading.value = true

        cyclicTaskStore.createCyclicTask(accountId.value, tasksPerHour.value, totalTaskCount.value, status.value)
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

    const modalHide = () => props.modalInstance.hide()
</script>
