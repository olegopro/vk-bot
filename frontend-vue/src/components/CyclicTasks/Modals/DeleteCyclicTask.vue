<template>
    <div class="modal fade" id="deleteCyclicTask" tabindex="-1" aria-labelledby="Delete task" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form @submit.prevent="deleteCyclicTaskById" class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="Delete task">Удаление задачи</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Удалить циклическую задачу c ID <strong>{{ taskId }}</strong></p>
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
    import { useCyclicTasksStore } from '../../../stores/CyclicTasksStore'

    const props = defineProps({
        modalInstance: Object,
        taskId: Number
    })

    const disable = ref(false)

    const cyclicTasksStore = useCyclicTasksStore()

    const deleteCyclicTaskById = () => {
        disable.value = true
        cyclicTasksStore.deleteCyclicTask(props.taskId)
            .then(() => modalHide())
            .finally(() => disable.value = false)
    }

    const modalHide = () => props.modalInstance.hide()
</script>
