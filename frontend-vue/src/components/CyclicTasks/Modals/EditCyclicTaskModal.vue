<script setup lang="ts">
  import { ref, computed, defineProps, watch, getCurrentInstance } from 'vue'
  import { useAccountsStore } from '@/stores/AccountsStore'
  import { useCyclicTasksStore } from '@/stores/CyclicTasksStore'
  import { useModal } from '@/composables/useModal'
  import { showSuccessNotification } from '@/helpers/notyfHelper'
  import TimePicker from '../TimePicker.vue'
  import { EditCyclicTaskRequest } from '@/models/CyclicTaskModel'

  const modalId = getCurrentInstance()?.type.__name
  const { closeModal } = useModal()

  const { taskId } = defineProps<{
    taskId: number
  }>()

  const accountsStore = useAccountsStore()
  const cyclicTaskStore = useCyclicTasksStore()

  const editedTaskData = ref<EditCyclicTaskRequest>({})

  const task = computed(() => cyclicTaskStore.fetchCyclicTasks.data?.find(task => task.id === taskId))
  watch(() => taskId, () => editedTaskData.value = { ...task.value }, { immediate: true })

  const editCyclicTask = () => {
    cyclicTaskStore.editCyclicTask.execute({
      taskId,
      taskData: {
        account_id: editedTaskData.value.account_id,
        total_task_count: editedTaskData.value.total_task_count,
        tasks_per_hour: editedTaskData.value.tasks_per_hour,
        status: editedTaskData.value.status,
        selected_times: editedTaskData.value.selected_times
      }
    }).then((response) => {
      closeModal(modalId)
      showSuccessNotification(response.message)
      cyclicTaskStore.fetchCyclicTasks.execute()
    })
  }

  const handleSelectedTimes = (times: any) => editedTaskData.value.selected_times = times
</script>

<template>
  <div class="modal fade" :id="modalId" tabindex="-1" aria-labelledby="Edit task" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form @submit.prevent="editCyclicTask" class="modal-content" v-if="editedTaskData">
        <div class="modal-header mb-1">
          <h1 class="modal-title fs-5" id="Delete task">Редактирование циклической задачи</h1>
          <button type="button" class="btn-close" @click="closeModal(modalId)" aria-label="Close"></button>
        </div>
        <PerfectScrollbar class="ps-modal">
          <div class="modal-body py-0">
            <select class="form-select mb-3" aria-label="Default select example" v-model="editedTaskData.account_id">
              <option v-for="account in accountsStore.fetchAccounts.data" :key="account.account_id" :value="account.account_id">
                {{ account.screen_name }} ({{ account.first_name }} {{ account.last_name }})
              </option>
            </select>

            <div class="input-group mb-3">
              <span class="input-group-text">Количество лайков (всего)</span>
              <input type="number" class="form-control" placeholder="По умолчанию 10 постов" v-model="editedTaskData.total_task_count">
            </div>

            <div class="input-group mb-3">
              <span class="input-group-text">Количество лайков в час</span>
              <input type="number" min="1" max="60" class="form-control" placeholder="По умолчанию 10 постов" v-model="editedTaskData.tasks_per_hour">
            </div>

            <select class="form-select mb-3" aria-label="Default select example" v-model="editedTaskData.status">
              <option value="active">Запущена</option>
              <option value="pause">На паузе</option>
              <option value="done">Выполнена</option>

            </select>

            <div class="accordion" id="accordionTimePicker">
              <div class="accordion-item">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTimePicker" aria-expanded="false" aria-controls="collapseTimePicker">
                    Расписание
                  </button>
                </h2>
                <div id="collapseTimePicker" class="accordion-collapse collapse" data-bs-parent="#accordionTimePicker">
                  <div class="accordion-body">
                    <TimePicker :initialSelectedTimes="editedTaskData.selected_times" @update:selectedTimes="handleSelectedTimes" />
                  </div>
                </div>
              </div>
            </div>

          </div>
        </PerfectScrollbar>
        <div class="modal-footer mt-1">
          <button type="button" class="btn btn-secondary" @click="closeModal(modalId)">Отмена</button>
          <button type="submit" class="btn btn-success" :disabled="cyclicTaskStore.editCyclicTask.loading">
            Сохранить
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
