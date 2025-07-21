<script setup>
  import { defineProps, toRefs } from 'vue'
  import TaskStatus from './TaskStatus.vue'
  import { format } from 'date-fns'
  import { useTasksStore } from '@/stores/TasksStore'
  import { useAccountStore } from '@/stores/AccountStore'
  import { useModal } from '../../composables/useModal'
  import { showErrorNotification } from '@/helpers/notyfHelper'
  import deleteTaskModal from './Modals/DeleteTaskModal.vue'
  import TaskDetailsModal from './Modals/TaskDetailsModal.vue'

  const props = defineProps({
    task: Object,
    showAccountDetailsModal: Function
  })

  const { task } = toRefs(props)

  const tasksStore = useTasksStore()
  const accountStore = useAccountStore()
  const { showModal } = useModal()

  const dateFormat = (date) => format(new Date(date), 'yyyy-MM-dd HH:mm:ss')

  const showTaskDetailsModal = (taskId) => {
    tasksStore.fetchTaskDetails(taskId)
      .then(() => showModal(TaskDetailsModal, { taskId }))
      .catch(error => showErrorNotification(error.response.data.message))
  }
</script>

<template>
  <tr>
    <th scope="row">ID {{ task.id }}</th>
    <td class="user-name inner-shadow">
      <div class="flex-container" @click="showAccountDetailsModal(task.account_id, task.owner_id, task.id)">
        {{ task.first_name }} {{ task.last_name }}

        <span v-if="accountStore.isOwnerDataLoading === task.id" class="spinner-border ms-2" role="status" style="width: 1rem; height: 1rem;">
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
        :disabled="tasksStore.isTaskDetailsLoading === task.id"
      >
        <span v-if="tasksStore.isTaskDetailsLoading === task.id" class="spinner-border" role="status" style="width: 1rem; height: 1rem;">
          <span class="visually-hidden">Loading...</span>
        </span>
        <i v-else class="bi bi-info-circle" />

      </button>

      <button class="btn btn-danger button-style" @click="showModal(deleteTaskModal, {taskId: task.id})">
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
