<template>
    <div class="modal fade" ref="modalRef" tabindex="-1" aria-labelledby="Delete all tasks" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form @submit.prevent="deleteTasks" class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="Delete task">Очистить очередь задач</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Удалить все задачи <strong>({{ tasks.length }})</strong></p>
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
    import { ref, onMounted } from 'vue'
    import { Modal } from 'bootstrap'
    import { useTasksStore } from '@/stores/TasksStore'

    const tasksStore = useTasksStore()
    const tasks = tasksStore.getTasks
    const deleteAllTasks = tasksStore.deleteAllTasks

    const disable = ref(false)
    const modalRef = ref(null)
    let modal

    onMounted(() => {
        modal = new Modal(modalRef.value)
    })

    const deleteTasks = async () => {
        disable.value = true

        await deleteAllTasks()

        modalHide()
        disable.value = false
    }

    const modalHide = () => {
        modal.hide()
    }
</script>
