<script setup lang="ts">
  import { useRoute, useRouter } from 'vue-router'
  import { onMounted, ref, watch, ComponentPublicInstance } from 'vue'
  import { useTasksStore } from '@/stores/TasksStore'
  import { useAccountStore } from '@/stores/AccountStore'
  import { useAccountsStore } from '@/stores/AccountsStore'
  import { showErrorNotification } from '@/helpers/notyfHelper'
  import { useModal } from '@/composables/useModal'
  import { useTasksRoute } from '@/composables/useTypedRoute'
  import RouterPaths from '@/router/routerPaths'
  import AddTask from '../components/Tasks/Modals/AddTask.vue'
  import TableThread from '../components/Tasks/TableThread.vue'
  import AccountDetailsModal from '../components/Tasks/Modals/AccountDetailsModal.vue'
  import DeleteAllTasksModal from '../components/Tasks/Modals/DeleteAllTasksModal.vue'
  import { TaskStatus } from '@/models/TaskModel'
  import { Nullable } from '@/types'

  const tasksStore = useTasksStore()
  const accountsStore = useAccountsStore()
  const accountStore = useAccountStore()
  const route = useRoute()
  const router = useRouter()
  const perfectScrollbarRef = ref<Nullable<ComponentPublicInstance>>(null)
  const { showModal } = useModal()
  const routeParams = useTasksRoute()

  const currentStatus = ref<TaskStatus>(routeParams.value.status || '')
  const selectedAccountId = ref<string>(routeParams.value.accountId || '')

  const accountDetailsData = ref<object>({})

  // Следим за изменениями статуса и ID аккаунта
  watch([currentStatus, selectedAccountId], () => {
    tasksStore.fetchTasks.execute({ status: currentStatus.value, accountId: selectedAccountId.value })
  })

  watch(route, () => perfectScrollbarRef.value && (perfectScrollbarRef.value.$el.scrollTop = 0))

  const filterTasks = (event) => {
    const status: TaskStatus = event.target.value || ''
    const accountId = selectedAccountId.value || ''
    router.push(RouterPaths.tasks({ status, accountId }))
    tasksStore.fetchTasks.execute({ status, accountId })
  }

  const filterByAccount = () => {
    const status = currentStatus.value || ''
    const accountId = selectedAccountId.value || ''
    router.push(RouterPaths.tasks({ status, accountId }))
    tasksStore.fetchTasks.execute({ status, accountId })
  }

  const showAccountDetailsModal = (accountId: number, ownerId: number, taskId: number | null) => {
    accountStore.fetchOwnerData.execute({ accountId, ownerId, taskId })
      .then(() => {
        const ownerData = accountStore.getOwnerDataById(ownerId)
        accountDetailsData.value = { ...ownerData }

        showModal(AccountDetailsModal, { accountData: accountDetailsData.value })
      })
      .catch(error => showErrorNotification(error.response.data.message))
  }

  const showDeleteAllTasksModal = () => {
    showModal(DeleteAllTasksModal, {
      selectedTasksStatus: currentStatus.value,
      selectedAccountId: selectedAccountId.value
    })

    tasksStore.getTasksCountByStatus.execute({
      status: currentStatus.value,
      accountId: selectedAccountId.value
    })
  }

  onMounted(() => {
    // processRouteParams()
    tasksStore.fetchTasks.execute({ status: currentStatus.value, accountId: selectedAccountId.value })
    accountsStore.fetchAccounts.execute()
  })
</script>

<template>
  route.params.status -- {{ route.params.status }}
  <div class="row mb-3 align-items-center">
    <div class="col d-flex align-items-center">
      <h1 class="h2 mb-0 position-relative">
        Список задач
      </h1>
      <button class="btn btn-sm btn-secondary btn-action my-0 ms-3"
        @click="router.push({ name: 'CyclicTasks' })"
      >
        <b>Циклические задачи</b>
      </button>
    </div>

    <div class="col d-flex justify-content-end">
      <select class="form-select me-3" style="width: 210px;" @change="filterTasks" v-model="currentStatus">
        <option value="">{{ tasksStore.totalTasksCount !== null ? `Все задачи (${tasksStore.totalTasksCount})` : 'Загрузка...' }}</option>
        <option value="failed">{{ tasksStore.totalTasksFailed !== null ? `C ошибками (${tasksStore.totalTasksFailed})` : 'Загрузка...' }}</option>
        <option value="queued">{{ tasksStore.totalTasksQueued !== null ? `В ожидании (${tasksStore.totalTasksQueued})` : 'Загрузка...' }}</option>
        <option value="done">{{ tasksStore.totalTasksDone !== null ? `Завершённые (${tasksStore.totalTasksDone})` : 'Загрузка...' }}</option>
      </select>

      <select class="form-select me-3" style="width: 280px" @change="filterByAccount" v-model="selectedAccountId">
        <option value="" :disabled="accountsStore.fetchAccounts.data?.length === 0">Все аккаунты</option>

        <option :value="selectedAccountId" v-if="accountsStore.accounts.length === 0 && accountsStore.fetchAccounts.loading" disabled>Загрузка...</option>
        <option v-else v-for="account in accountsStore.fetchAccounts.data" :key="account.account_id" :value="account.account_id">
          {{ account.screen_name }} ({{ account.first_name }} {{ account.last_name }})
        </option>
      </select>

      <button class="btn btn-danger btn-action me-3"
        :disabled="tasksStore.tasks.length === 0"
        @click="showDeleteAllTasksModal"
      >
        Удалить задачи
      </button>

      <button class="btn btn-success btn-action"
        type="button"
        @click="showModal(AddTask)"
      >
        Добавить задачу
      </button>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <PerfectScrollbar ref="perfectScrollbarRef" class="ps-table">
        <table class="table table-hover">
          <thead>
            <tr>
              <th scope="col" style="width: 110px;">
                <h6 class="d-flex mb-0 pe-none justify-content-center">
                  <span class="badge btn btn-secondary d-flex items-center justify-content-center fw-bold"
                    style="padding: 8px; min-width: 80px;">
                    <template v-if="tasksStore.isLoading && !tasksStore.tasks.length">
                      <span class="spinner-border" role="status" style="width: 12px; height: 12px;">
                        <span class="visually-hidden">Загрузка...</span>
                      </span>
                    </template>

                    <template v-else>
                      <span>{{ tasksStore.tasks.length }}</span>
                    </template>
                  </span>
                </h6>

              </th>
              <th scope="col" style="width: 350px;">Имя и фамилия</th>
              <th scope="col" style="width: 100px;">Статус</th>
              <th scope="col" style="width: 250px;">Действия</th>
              <th scope="col" style="width: 250px">Старт задачи</th>
              <th scope="col">Задача создана</th>
            </tr>
          </thead>

          <tbody>
            <TableThread
              v-for="task in tasksStore.tasks"
              :task="task"
              :key="task.id"
              :showAccountDetailsModal="showAccountDetailsModal"
            />

            <tr v-if="tasksStore.isLoading" style="height: 55px;">
              <td colspan="7">
                <div class="spinner-border" role="status" style="position: relative; top: 3px;">
                  <span class="visually-hidden">Загрузка...</span>
                </div>
              </td>
            </tr>

            <tr v-if="tasksStore.tasks.length === 0 && !tasksStore.isLoading" class="pe-none">
              <td colspan="7" style="height: 55px;">
                Список задач пуст
              </td>
            </tr>
          </tbody>
        </table>
      </PerfectScrollbar>
    </div>
  </div>
</template>
