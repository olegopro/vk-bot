<template>
    <div class="modal fade" id="deleteAllCyclicTasksModal" tabindex="-1" aria-labelledby="Delete all tasks" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form @submit.prevent="deleteCyclicTasks" class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="Delete task">Очистить очередь циклических задач</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Удалить все циклические задачи <strong>({{ cyclicTasksStore.totalCyclicTasksCount }})</strong></p>
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
    import { ref, onMounted, onUnmounted, inject } from 'vue'
    import { useCyclicTasksStore } from '@/stores/CyclicTasksStore'

    const disable = ref(false)
    const cyclicTasksStore = useCyclicTasksStore()

    const closeModal = inject('closeModal')

    const deleteCyclicTasks = () => {
        disable.value = true
        cyclicTasksStore.deleteAllCyclicTasks()
            .then(() => modalHide())
            .finally(() => disable.value = false)
    }

    const modalHide = () => closeModal('deleteAllCyclicTasksModal')

    onMounted(() => console.log('DeleteAllCyclicTasks onMounted'))
    onUnmounted(() => console.log('DeleteAllCyclicTasks onUnmounted'))
</script>
