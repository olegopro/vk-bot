<template>
    <div class="modal fade" id="deleteAllTasks" tabindex="-1" aria-labelledby="Delete all tasks" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form @submit.prevent="deleteTasks" class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="Delete task">Очистить очередь задач</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Удалить все задачи <strong>({{ tasksStore.tasks.length }})</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="modalHide">Отмена</button>
                    <button type="submit" class="btn btn-danger" :disabled="disable">Очистить</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
    import { ref, defineProps } from 'vue'
    import { useTasksStore } from '@/stores/TasksStore'

    const props = defineProps({
        modalInstance: Object,
        selectedTasksStatus: String,
        selectedAccountId: String
    })

    const disable = ref(false)

    const tasksStore = useTasksStore()

    const deleteTasks = () => {
        disable.value = true
        tasksStore.deleteAllTasks(props.selectedTasksStatus, props.selectedAccountId)
            .then(() => modalHide())
            .finally(() => disable.value = false)
    }

    const modalHide = () => props.modalInstance.hide()
</script>
