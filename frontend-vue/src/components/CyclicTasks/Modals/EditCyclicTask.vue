<template>
    <div class="modal fade" id="editCyclicTaskModal" tabindex="-1" aria-labelledby="Add task" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form @submit.prevent="editCyclicTask" class="modal-content" v-if="editedTaskData">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="Delete task">Редактирование циклической задачи</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <select class="form-select mb-3" aria-label="Default select example" v-model="editedTaskData.account_id">
                        <option v-for="account in accountsStore.accounts" :key="account.id" :value="account.account_id">
                            {{ account.screen_name }} ({{ account.first_name }} {{ account.last_name }})
                        </option>
                    </select>

                    <div class="input-group mb-3">
                        <span class="input-group-text">Количество лайков (всего)</span>
                        <input type="number" class="form-control" placeholder="По умолчанию 10 постов" v-model="editedTaskData.tasks_count">
                    </div>

                    <div class="input-group mb-3">
                        <span class="input-group-text">Количество лайков в час</span>
                        <input type="number" min="1" max="60" class="form-control" placeholder="По умолчанию 10 постов" v-model="editedTaskData.tasks_per_hour">
                    </div>

                    <select class="form-select mb-3" aria-label="Default select example" v-model="editedTaskData.status">
                        <option value="active">Запущена</option>
                        <option value="pause">На паузе</option>
                        <option value="done">Выполнена</option>

                    </select>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="modalHide">Отмена</button>
                    <button type="submit" class="btn btn-success" :disabled="disablePost">
                        Сохранить
                        <span v-if="loading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
    import { ref, computed, defineProps, watch } from 'vue'
    import { useAccountsStore } from '../../../stores/AccountsStore'
    import { useCyclicTasksStore } from '../../../stores/CyclicTasksStore'
    import { showErrorNotification } from '../../../helpers/notyfHelper'

    const props = defineProps({
        modalInstance: Object,
        taskId: Number
    })

    const accountsStore = useAccountsStore()
    const cyclicTaskStore = useCyclicTasksStore()

    const disablePost = ref(false)
    const loading = ref(false)
    const editedTaskData = ref(null)

    const task = computed(() => cyclicTaskStore.getTaskById(props.taskId))
    watch(() => props.taskId, () => editedTaskData.value = { ...task.value }, { immediate: true })

    const editCyclicTask = () => {
        disablePost.value = true
        loading.value = true

        cyclicTaskStore.editCyclicTask(props.taskId, {
            account_id: editedTaskData.value.account_id,
            tasks_count: editedTaskData.value.tasks_count,
            tasks_per_hour: editedTaskData.value.tasks_per_hour,
            status: editedTaskData.value.status
        })
            .then(() => modalHide())
            .catch(error => showErrorNotification(error.message))
    }

    const modalHide = () => {
        props.modalInstance.hide()

        loading.value = false
        disablePost.value = false
    }
</script>
