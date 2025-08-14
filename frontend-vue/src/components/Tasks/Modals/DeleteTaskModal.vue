<script setup lang="ts">
  import { ref, defineProps, getCurrentInstance } from 'vue'
  import { useTasksStore } from '@/stores/TasksStore'
  import { useModal } from '@/composables/useModal'
  import { showSuccessNotification } from '@/helpers/notyfHelper'

  const { taskId } = defineProps<{ taskId: number }>()

  const modalId = getCurrentInstance()?.type.__name
  const disable = ref<boolean>(false)
  const tasksStore = useTasksStore()
  const { closeModal } = useModal()

  const deleteTaskById = () => {
    disable.value = true

    tasksStore.deleteTask.execute({ taskId })
      .then((response) => {
        closeModal(modalId)
        tasksStore.fetchTasks.execute()
        showSuccessNotification(response.message)
      })
      .finally(() => disable.value = false)
  }
</script>

<template>
  <div class="modal fade" :id="modalId" tabindex="-1" aria-labelledby="Delete task" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form @submit.prevent="deleteTaskById" class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="Delete task">Удаление задачи</h1>
          <button type="button" class="btn-close" @click="closeModal(modalId)" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-0">Удалить задачу c ID <strong>{{ taskId }}</strong></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeModal(modalId)">Отмена</button>
          <button type="submit" class="btn btn-danger" :disabled="disable">Удалить</button>
        </div>
      </form>
    </div>
  </div>
</template>
