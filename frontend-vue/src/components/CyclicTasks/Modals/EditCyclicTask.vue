<script setup>
  import { ref, computed, defineProps, watch, onMounted, onUnmounted, inject } from 'vue'
  import { useAccountsStore } from '../../../stores/AccountsStore'
  import { useCyclicTasksStore } from '../../../stores/CyclicTasksStore'
  import { showErrorNotification } from '../../../helpers/notyfHelper'
  import TimePicker from '../TimePicker.vue'

  const props = defineProps({ taskId: Number })

  const accountsStore = useAccountsStore()
  const cyclicTaskStore = useCyclicTasksStore()

  const disablePost = ref(false)
  const loading = ref(false)
  const editedTaskData = ref(null)
  const selectedTimesForTimePicker = ref(null)

  const closeModal = inject('closeModal')

  const task = computed(() => cyclicTaskStore.getTaskById(props.taskId))
  watch(() => props.taskId, () => editedTaskData.value = { ...task.value }, { immediate: true })

  watch(() => editedTaskData.value.id, (newId, oldId) => {
    if (newId !== oldId) {
      selectedTimesForTimePicker.value = { ...editedTaskData.value.selected_times }
    }
  }, { immediate: true })

  const editCyclicTask = () => {
    disablePost.value = true
    loading.value = true

    cyclicTaskStore.editCyclicTask(props.taskId, {
      account_id: editedTaskData.value.account_id,
      total_task_count: editedTaskData.value.total_task_count,
      tasks_per_hour: editedTaskData.value.tasks_per_hour,
      status: editedTaskData.value.status,
      selected_times: editedTaskData.value.selected_times
    })
      .then(() => modalHide())
      .catch(error => showErrorNotification(error.message))
  }

  const handleSelectedTimes = times => editedTaskData.value.selected_times = times

  const modalHide = () => {
    closeModal('editCyclicTaskModal')

    loading.value = false
    disablePost.value = false
  }

  onMounted(() => console.log('EditCyclicTask onMounted'))
  onUnmounted(() => console.log('EditCyclicTask onUnmounted'))
</script>

<template>
  <div class="modal fade" id="editCyclicTaskModal" tabindex="-1" aria-labelledby="Edit task" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form @submit.prevent="editCyclicTask" class="modal-content" v-if="editedTaskData">
        <div class="modal-header mb-1">
          <h1 class="modal-title fs-5" id="Delete task">Редактирование циклической задачи</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <PerfectScrollbar class="ps-modal">
          <div class="modal-body py-0">
            <select class="form-select mb-3" aria-label="Default select example" v-model="editedTaskData.account_id">
              <option v-for="account in accountsStore.accounts" :key="account.id" :value="account.account_id">
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
          <button type="button" class="btn btn-secondary" @click="modalHide">Отмена</button>
          <button type="submit" class="btn btn-success" :disabled="disablePost">
            Сохранить
            <span v-if="loading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
