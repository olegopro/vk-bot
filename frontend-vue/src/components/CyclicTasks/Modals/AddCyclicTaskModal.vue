<script setup>
  import { ref, getCurrentInstance } from 'vue'
  import { useAccountsStore } from '@/stores/AccountsStore'
  import { showSuccessNotification } from '@/helpers/notyfHelper'
  import { useCyclicTasksStore } from '@/stores/CyclicTasksStore'
  import TimePicker from '@/components/CyclicTasks/TimePicker.vue'
  import { useModal } from '@/composables/useModal'

  const accountsStore = useAccountsStore()
  const cyclicTaskStore = useCyclicTasksStore()

  const modalId = getCurrentInstance()?.type.__name

  const accountId = ref('selectAccount')
  const totalTaskCount = ref(1000)
  const tasksPerHour = ref(60)
  const status = ref('active')
  const selectedTimes = ref({})

  const { closeModal } = useModal()

  const addNewTask = () => {
    cyclicTaskStore.createCyclicTask.execute(
      {
        account_id: accountId.value,
        tasks_per_hour: tasksPerHour.value,
        total_task_count: totalTaskCount.value,
        status: status.value,
        selected_times: selectedTimes.value
      }
    )
      .then(response => {
        showSuccessNotification(response.message)
        closeModal(modalId)
        accountId.value = 'selectAccount'
        cyclicTaskStore.fetchCyclicTasks.execute()
      })
  }

  const handleSelectedTimes = times => selectedTimes.value = times
</script>

<template>

  <div class="modal fade" :id="modalId" tabindex="-1" aria-labelledby="Add task" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form @submit.prevent="addNewTask" class="modal-content">
        <div class="modal-header mb-1">
          <h1 class="modal-title fs-5" id="Delete task">Добавление циклической задачи</h1>
          <button type="button" class="btn-close" @click="closeModal(modalId)" aria-label="Close"></button>
        </div>

        <PerfectScrollbar class="ps-modal">
          <div class="modal-body py-0">
            <select class="form-select mb-3" v-model="accountId">
              <option disabled value="selectAccount">Выберите аккаунт</option>
              <option v-for="account in accountsStore.fetchAccounts.data" :key="account.id" :value="account.account_id">
                {{ account.screen_name }} ({{ account.first_name }} {{ account.last_name }})
              </option>
            </select>

            <div class="input-group mb-3">
              <span class="input-group-text">Количество лайков (всего)</span>
              <input type="number" class="form-control" placeholder="По умолчанию 10 постов" v-model="totalTaskCount">
            </div>

            <div class="input-group mb-3">
              <span class="input-group-text">Количество лайков в час</span>
              <input type="number" min="1" max="60" class="form-control" placeholder="По умолчанию 10 постов" v-model="tasksPerHour">
            </div>

            <select class="form-select mb-3" v-model="status">
              <option selected value="active">Запустить сейчас</option>
              <option value="pause">Оставить на паузе</option>
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
                    <TimePicker @update:selectedTimes="handleSelectedTimes" />
                  </div>
                </div>
              </div>
            </div>

          </div>
        </PerfectScrollbar>

        <div class="modal-footer mb-1">
          <button type="button" class="btn btn-secondary" @click=closeModal(modalId)>Отмена</button>
          <button type="submit" class="btn btn-success" :disabled="cyclicTaskStore.fetchCyclicTasks.loading">
            Создать
          </button>
        </div>

      </form>
    </div>
  </div>
</template>
