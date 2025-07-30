<script setup>
  import { onMounted, provide, ref, shallowRef } from 'vue'
  import { useCyclicTasksStore } from '../stores/CyclicTasksStore'
  import { useAccountsStore } from '../stores/AccountsStore'
  import TableThread from '../components/CyclicTasks/TableThread.vue'
  import DeleteCyclicTask from '../components/CyclicTasks/Modals/DeleteCyclicTask.vue'
  import AddCyclicTaskModal from '../components/CyclicTasks/Modals/AddCyclicTaskModal.vue'
  import EditCyclicTask from '../components/CyclicTasks/Modals/EditCyclicTask.vue'
  import DeleteAllCyclicTasks from '../components/CyclicTasks/Modals/DeleteAllCyclicTasks.vue'
  import router from '../router'
  import { useModal } from '@/composables/useModal.ts'

  const { isOpen, preparedModal, showModal, closeModal } = useModal()

  const taskId = ref(null)
  const modalComponent = shallowRef(null)

  const cyclicTasksStore = useCyclicTasksStore()
  const accountsStore = useAccountsStore()

  provide('closeModal', closeModal)
  const showDeleteCyclicTaskModal = (id) => {
    taskId.value = id

    modalComponent.value = preparedModal(DeleteCyclicTask)
    showModal('deleteCyclicTaskModal')
  }

  const showEditCyclicTaskModal = (id) => {
    taskId.value = id

    modalComponent.value = preparedModal(EditCyclicTask)
    showModal('editCyclicTaskModal')
  }

  const showDeleteAllCyclicTasksModal = () => {
    modalComponent.value = preparedModal(DeleteAllCyclicTasks)
    showModal('deleteAllCyclicTasksModal')
  }

  onMounted(() => {
    cyclicTasksStore.fetchCyclicTasks.execute()
    accountsStore.fetchAccounts.execute()
  })
</script>

<template>
  <div class="row mb-3 align-items-center">
    <div class="col d-flex align-items-center">
      <h1 class="h2 mb-0">
        Список задач
      </h1>
      <button class="btn btn-sm btn-primary btn-action my-0 ms-3 me-3"
        @click="router.push({ name: 'Tasks' })"
      >
        <b>Циклические задачи</b>
      </button
      >

      <h6 class="mb-0" style="pointer-events: none">
        <span class="badge btn btn-secondary d-flex items-center fw-bold"
          style="padding: 8px"
        >
          <template v-if="cyclicTasksStore.fetchCyclicTasks.loading && !cyclicTasksStore.fetchCyclicTasks.data?.length">
            <span class="spinner-border" role="status" style="width: 12px; height: 12px;">
              <span class="visually-hidden">Загрузка...</span>
            </span>
          </template>

          <template v-else>
            {{ cyclicTasksStore.fetchCyclicTasks.data?.length }}
          </template>
        </span>
      </h6>
    </div>

    <div class="col d-flex justify-content-end">
      <button class="btn btn-danger btn-action me-3"
        :disabled="cyclicTasksStore.fetchCyclicTasks.data?.length === 0"
        @click="showDeleteAllCyclicTasksModal"
      >
        Очистить список
      </button>

      <button class="btn btn-success btn-action"
        type="button"
        @click="showModal(AddCyclicTaskModal)"
      >
        Добавить задачу
      </button>
    </div>
  </div>

  <div class="row">
    <div class="col-12">

      <PerfectScrollbar class="ps-table">
        <table v-if="cyclicTasksStore.fetchCyclicTasks.data" class="table table-hover">
          <thead>
            <tr>
              <th scope="col" style="width: 110px;">#</th>
              <th scope="col" style="width: auto;">Аккаунт</th>
              <th scope="col" style="width: 110px;">Количество</th>
              <th scope="col" style="width: 110px;">Осталось</th>
              <th scope="col" style="width: 120px;">Задач в час</th>
              <th scope="col" style="width: 150px;">Статус задачи</th>
              <th scope="col" style="width: 160px;">Действия</th>
              <th scope="col" style="width: 150px">Старт задачи</th>
              <th scope="col" style="width: 150px">Задача создана</th>
            </tr>
          </thead>

          <tbody>
            <TableThread
              v-for="cyclicTask in cyclicTasksStore.fetchCyclicTasks.data"
              :cyclicTask="cyclicTask"
              :key="cyclicTask.id"
              :showDeleteCyclicTaskModal="showDeleteCyclicTaskModal"
              :showEditCyclicTaskModal="showEditCyclicTaskModal"
            />

            <tr v-if="cyclicTasksStore.fetchCyclicTasks.loading" style="height: 55px;">
              <td colspan="9">
                <div class="spinner-border" role="status" style="position: relative; top: 3px;">
                  <span class="visually-hidden">Загрузка...</span>
                </div>
              </td>
            </tr>

            <tr v-if="cyclicTasksStore.fetchCyclicTasks.data?.length === 0 && !cyclicTasksStore.fetchCyclicTasks.loading" class="pe-none">
              <td colspan="9" style="height: 55px;">
                Список циклических задач пуст
              </td>
            </tr>

          </tbody>
        </table>
      </PerfectScrollbar>

    </div>
  </div>

  <Teleport to="body">
    <component v-if="isOpen"
      @mounted="showModal"
      :is="modalComponent"
      :taskId="taskId"
    />
  </Teleport>

</template>
