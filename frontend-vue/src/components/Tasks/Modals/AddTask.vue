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

    const accountStore = useAccountStore()
    const accountsStore = useAccountsStore()
    const tasksStore = useTasksStore()

    const accountId = ref('selectAccount')
    const disablePost = ref(true)
    const loading = ref(false)
    const taskCount = ref(10)

    const closeModal = inject('closeModal')

    watch(accountId, newVal => disablePost.value = newVal === 'selectAccount')

    const addNewTask = () => {
        disablePost.value = true
        loading.value = true

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

    const modalHide = () => closeModal('addTaskModal')

    onMounted(() => console.log('AddTaskModal onMounted'))
    onUnmounted(() => console.log('AddTaskModal onUnmounted'))
</script>
