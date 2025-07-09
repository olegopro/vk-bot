<script setup>
  import { defineProps, toRefs, computed } from 'vue'
  import TaskStatus from './TaskStatus.vue'
  import { format } from 'date-fns'
  import { useCyclicTasksStore } from '@/stores/CyclicTasksStore'
  import router from '../../router'

  const cyclicTasksStore = useCyclicTasksStore()

  const props = defineProps({
    cyclicTask: Object,
    showDeleteCyclicTaskModal: Function,
    showEditCyclicTaskModal: Function
  })

  const { cyclicTask } = toRefs(props)
  const navigateToAccount = (accountId) => router.push({ name: 'Account', params: { id: accountId } })
  const dateFormat = (date) => format(new Date(date), 'yyyy-MM-dd HH:mm:ss')
  const buttonClass = computed(() => cyclicTask.value.status === 'active' ? 'btn-secondary' : 'btn-success')
  const buttonIcon = computed(() => cyclicTask.value.status === 'active' ? 'bi-pause-circle' : 'bi-play-circle')
</script>

<template>
  <tr>
    <th scope="row">ID {{ cyclicTask.id }}</th>

    <td >
      <span style="cursor: pointer;" @click="navigateToAccount(cyclicTask.account_id)">
        {{ cyclicTask.first_name }} {{ cyclicTask.last_name }}
      </span>
    </td>

    <td>
      {{ cyclicTask.total_task_count }}
    </td>

    <td>
      {{ cyclicTask.remaining_tasks_count }}
    </td>

    <td>
      {{ cyclicTask.tasks_per_hour }}
    </td>

    <td>
      <TaskStatus :type="cyclicTask.status" :errorMessage="cyclicTask.error_message" />
    </td>

    <td>
      <button
        :class="['btn', 'button-style', 'me-2', buttonClass]"
        type="button"
        @click="cyclicTasksStore.pauseCyclicTask(cyclicTask.id)"
      >
        <i :class="['bi', buttonIcon]" />
      </button>

      <button
        class="btn btn-primary button-style me-2"
        type="button"
        @click="props.showEditCyclicTaskModal(cyclicTask.id)"
      >
        <i class="bi bi-pencil" />
      </button>

      <button class="btn btn-danger button-style" @click="props.showDeleteCyclicTaskModal(cyclicTask.id)">
        <i class="bi bi-trash3" />
      </button>
    </td>

    <td>{{ dateFormat(cyclicTask.started_at) }}</td>
    <td>{{ dateFormat(cyclicTask.created_at) }}</td>
  </tr>
</template>
