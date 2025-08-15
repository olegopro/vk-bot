<script setup lang="ts">
  import { defineProps, computed } from 'vue'
  import TaskStatus from './TaskStatus.vue'
  import { format } from 'date-fns'
  import { useCyclicTasksStore } from '@/stores/CyclicTasksStore'
  import { useModal } from '@/composables/useModal'
  import EditCyclicTaskModal from './Modals/EditCyclicTaskModal.vue'
  import DeleteCyclicTaskModalModal from './Modals/DeleteCyclicTaskModal.vue'
  import router from '../../router'
  import type { CyclicTask } from '@/types/tasks'

  const { cyclicTask } = defineProps<{ cyclicTask: CyclicTask }>()

  const cyclicTasksStore = useCyclicTasksStore()
  const { showModal } = useModal()

  const navigateToAccount = (accountId: number) => router.push({ name: 'Account', params: { id: accountId } })
  const dateFormat = (date: string) => format(new Date(date), 'yyyy-MM-dd HH:mm:ss')
  const buttonClass = computed(() => cyclicTask.status === 'active' ? 'btn-secondary' : 'btn-success')
  const buttonIcon = computed(() => cyclicTask.status === 'active' ? 'bi-pause-circle' : 'bi-play-circle')

  const handlePauseTask = async () => {
    await cyclicTasksStore.pauseCyclicTask.execute({ taskId: cyclicTask.id })
    await cyclicTasksStore.fetchCyclicTasks.execute()
  }
</script>

<template>
  <tr>
    <th scope="row">ID {{ cyclicTask.id }}</th>

    <td >
      <span style="cursor: pointer;" @click="navigateToAccount(cyclicTask.account_id)">
        {{ cyclicTask.first_name }} {{ cyclicTask.last_name }}
      </span>
    </td>

    <td>{{ cyclicTask.total_task_count }}</td>
    <td>{{ cyclicTask.remaining_tasks_count }}</td>
    <td>{{ cyclicTask.tasks_per_hour }}</td>

    <td>
      <TaskStatus :type="cyclicTask.status" :errorMessage="undefined" />
    </td>

    <td>
      <button
        :class="['btn', 'button-style', 'me-2', buttonClass]"
        type="button"
        @click="handlePauseTask"
      >
        <i :class="['bi', buttonIcon]" />
      </button>

      <button
        class="btn btn-primary button-style me-2"
        type="button"
        @click="showModal(EditCyclicTaskModal, { taskId: cyclicTask.id })"
      >
        <i class="bi bi-pencil" />
      </button>

      <button class="btn btn-danger button-style" @click="showModal(DeleteCyclicTaskModalModal, { taskId: cyclicTask.id })">
        <i class="bi bi-trash3" />
      </button>
    </td>

    <td>{{ dateFormat(cyclicTask.started_at) }}</td>
    <td>{{ dateFormat(cyclicTask.created_at) }}</td>
  </tr>
</template>
