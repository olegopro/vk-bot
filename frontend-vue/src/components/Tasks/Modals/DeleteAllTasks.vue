<template>
    <div class="modal fade" id="deleteAllTasksModal" tabindex="-1" aria-labelledby="Delete all tasks" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form @submit.prevent="deleteTasks" class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="Delete task">Удалить задачи</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">
                        Удалить <strong>{{ tasksStore.taskCountByStatus }}</strong> задачи
                        <span v-if="props.selectedTasksStatus">со статусом <strong>{{ props.selectedTasksStatus }}</strong></span>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="modalHide">Отмена</button>
                    <button type="submit" class="btn btn-danger" :disabled="disable">Удалить</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
    import { ref, defineProps, inject, onMounted, onUnmounted } from 'vue'
    import { useTasksStore } from '@/stores/TasksStore'

    const props = defineProps({
        selectedTasksStatus: String,
        selectedAccountId: String
    })

    const disable = ref(false)
    const tasksStore = useTasksStore()

    const closeModal = inject('closeModal')

    const deleteTasks = () => {
        disable.value = true
        tasksStore.deleteAllTasks(props.selectedTasksStatus, props.selectedAccountId)
            .then(() => modalHide())
            .finally(() => disable.value = false)
    }

    const modalHide = () => closeModal('deleteAllTasksModal')

    onMounted(() => console.log('DeleteAllTasks onMounted'))
    onUnmounted(() => console.log('DeleteAllTasks onUnmounted'))
</script>
