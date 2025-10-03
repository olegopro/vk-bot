<script setup lang="ts">
  import { defineProps } from 'vue'
  import TaskStatus from '@/components/Tasks/TaskStatus.vue'
  import { format } from 'date-fns'
  import { useTasksStore } from '@/stores/TasksStore'
  import { useAccountStore } from '@/stores/AccountStore'
  import DeleteTaskModal from '@/components/Tasks/Modals/DeleteTaskModal.vue'
  import TaskDetailsModal from '@/components/Tasks/Modals/TaskDetailsModal.vue'
  import AccountDetailsModal from '@/components/Tasks/Modals/AccountDetailsModal.vue'
  import type { Task } from '@/types/tasks'
  import { useModal } from '@/composables/useModal'

  const { task } = defineProps<{ task: Task }>()

  const tasksStore = useTasksStore()
  const accountStore = useAccountStore()
  const { showModal } = useModal()

  const dateFormat = (date: string | number | Date) => format(new Date(date), 'yyyy-MM-dd HH:mm:ss')

  const showTaskDetailsModal = (taskId: number): void => {
    tasksStore.fetchTaskDetails.execute({ taskId }, String(taskId))
      .then(() => showModal(TaskDetailsModal, { taskId }))
  }

  const showAccountDetailsModal = (accountId: number, ownerId: number) => {
    accountStore.fetchOwnerData.execute({ ownerId })
      .then(() => showModal(AccountDetailsModal))
  }
</script>

<template>
  <tr>
    <th scope="row">ID {{ task.id }}</th>
    <td class="user-name inner-shadow">
      <div class="flex-container" @click="showAccountDetailsModal(task.account_id, task.owner_id)">
        {{ task.first_name }} {{ task.last_name }}

        <span v-if="accountStore.fetchOwnerData.isLoadingKey(String(task.id))" class="spinner-border ms-2" role="status" style="width: 1rem; height: 1rem;">
          <span class="visually-hidden">Loading...</span>
        </span>
        <i v-else class="bi bi-person-circle ms-2" style="font-size: 1rem; opacity: 0.75" />
      </div>
    </td>

    <td>
      <TaskStatus :type="task.status" :errorMessage="task.error_message" />
    </td>

    <td>
      <button
        class="btn btn-primary button-style me-2"
        type="button"
        @click="showTaskDetailsModal(task.id)"
        :disabled="tasksStore.fetchTaskDetails.loading && tasksStore.fetchTaskDetails.isLoadingKey(task.id.toString())"
      >
        <span v-if="tasksStore.fetchTaskDetails.loading && tasksStore.fetchTaskDetails.isLoadingKey(task.id.toString())" class="spinner-border" role="status" style="width: 1rem; height: 1rem;">
          <span class="visually-hidden">Loading...</span>
        </span>
        <i v-else class="bi bi-info-circle" />

      </button>

      <button class="btn btn-danger button-style" @click="showModal(DeleteTaskModal, { taskId: task.id })">
        <i class="bi bi-trash3" />
      </button>
    </td>

    <td>{{ dateFormat(task.run_at) }}</td>
    <td>{{ dateFormat(task.created_at) }}</td>
  </tr>
</template>

<style scoped lang="scss">
  .flex-container {
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    width: fit-content;
    margin-left: auto;
    margin-right: auto;
  }
</style>
