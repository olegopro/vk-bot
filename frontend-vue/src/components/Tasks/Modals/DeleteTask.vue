<template>
    <div class="modal fade" id="deleteTask" tabindex="-1" aria-labelledby="Delete task" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form @submit.prevent="deleteTaskById" class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="Delete task">Удаление задачи</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Удалить задачу c ID <strong>{{ taskId }}</strong></p>
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
    import { ref, defineProps } from 'vue'
    import { useTasksStore } from '@/stores/TasksStore'

    const props = defineProps({
        modalInstance: Object,
        taskId: Number
    })

    const disable = ref(false)

    const tasksStore = useTasksStore()

    const deleteTaskById = () => {
        disable.value = true
        tasksStore.deleteTask(props.taskId)
            .then(() => props.modalInstance.hide())
            .finally(() => disable.value = false)
    }

    const modalHide = () => props.modalInstance.hide()
</script>
