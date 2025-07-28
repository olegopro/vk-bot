<script setup lang="ts">
  import { ref, defineProps, getCurrentInstance } from 'vue'
  import { useTasksStore } from '@/stores/TasksStore'
  import { useModal } from '@/composables/useModal'
  import { TaskStatus } from '@/models/TaskModel'

  const { selectedTasksStatus, selectedAccountId } = defineProps<{
    selectedTasksStatus: TaskStatus,
    selectedAccountId: string
  }>()

  const modalId = getCurrentInstance()?.type.__name
  const disable = ref(false)
  const tasksStore = useTasksStore()
  const { closeModal } = useModal()

  const deleteTasks = () => {
    disable.value = true
    tasksStore.deleteAllTasks(selectedTasksStatus, selectedAccountId)
      .then(() => closeModal(modalId))
      .finally(() => disable.value = false)
  }
</script>

<template>
  <div class="modal fade" :id="modalId" tabindex="-1" aria-labelledby="Delete all tasks" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form @submit.prevent="deleteTasks" class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="Delete task">Удалить задачи</h1>
          <button type="button" class="btn-close" @click="closeModal(modalId)" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-0">
            Удалить <strong>{{ tasksStore.taskCountByStatus }}</strong> задачи
            <span v-if="selectedTasksStatus">со статусом <strong>{{ selectedTasksStatus }}</strong></span>
          </p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeModal(modalId)">Отмена</button>
          <button type="submit" class="btn btn-danger" :disabled="disable">Удалить</button>
        </div>
      </form>
    </div>
  </div>
</template>
