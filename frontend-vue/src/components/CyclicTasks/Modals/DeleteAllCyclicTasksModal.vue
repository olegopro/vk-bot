<script setup lang="ts">
  import { getCurrentInstance } from 'vue'
  import { useCyclicTasksStore } from '@/stores/CyclicTasksStore'
  import { useModal } from '@/composables/useModal'
  import { showSuccessNotification } from '@/helpers/notyfHelper'

  const modalId = getCurrentInstance()?.type.__name
  const { closeModal } = useModal()
  const cyclicTasksStore = useCyclicTasksStore()

  const deleteCyclicTasks = () => cyclicTasksStore.deleteAllCyclicTasks.execute()
    .then(response => {
      closeModal(modalId)
      showSuccessNotification(response.message)
      cyclicTasksStore.fetchCyclicTasks.execute()
    })
</script>

<template>
  <div class="modal fade" :id="modalId" tabindex="-1" aria-labelledby="Delete all tasks" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form @submit.prevent="deleteCyclicTasks" class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="Delete all tasks">Очистить очередь циклических задач</h1>
          <button type="button" class="btn-close" @click="closeModal(modalId)" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-0">Удалить все циклические задачи <strong>({{ cyclicTasksStore.fetchCyclicTasks.data?.length ?? 0 }})</strong></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeModal(modalId)">Отмена</button>
          <button type="submit" class="btn btn-danger">Удалить</button>
        </div>
      </form>
    </div>
  </div>
</template>
