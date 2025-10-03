<script setup lang="ts">
  import { useRoute, useRouter } from 'vue-router'
  import { onMounted, ref, watch, ComponentPublicInstance } from 'vue'
  import { useTasksStore } from '@/stores/TasksStore'
  import { useAccountsStore } from '@/stores/AccountsStore'
  import { useModal } from '@/composables/useModal'
  import { useTasksRoute } from '@/composables/useTypedRoute'
  import RouterPaths from '@/router/routerPaths'
  import TableThread from '@/components/Tasks/TableThread.vue'
  import DeleteAllTasksModal from '@/components/Tasks/Modals/DeleteAllTasksModal.vue'
  import { TaskStatus } from '@/models/TaskModel'
  import { Nullable } from '@/types'
  import AddTaskModal from '@/components/Tasks/Modals/AddTaskModal.vue'

  const tasksStore = useTasksStore()
  const accountsStore = useAccountsStore()
  const route = useRoute()
  const router = useRouter()
  const perfectScrollbarRef = ref<Nullable<ComponentPublicInstance>>(null)
  const { showModal } = useModal()
  const routeParams = useTasksRoute()

  const currentStatus = ref<TaskStatus>(routeParams.value.status || '')
  const selectedAccountId = ref<string>(routeParams.value.accountId || '')

  // Следим за изменениями статуса и ID аккаунта
  watch([currentStatus, selectedAccountId], () => {
    tasksStore.fetchTasks.execute({ status: currentStatus.value, accountId: selectedAccountId.value })
  })

  watch(route, () => perfectScrollbarRef.value && (perfectScrollbarRef.value.$el.scrollTop = 0))

  const filterTasks = (event: Event) => {
    const target = event.target as HTMLSelectElement
    const status: TaskStatus = target.value as TaskStatus || ''
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

  const showDeleteAllTasksModal = () => {
    showModal(DeleteAllTasksModal, {
      selectedTasksStatus: currentStatus.value,
      selectedAccountId: selectedAccountId.value
    })
  }

  onMounted(() => {
    tasksStore.fetchTasks.execute({ status: currentStatus.value, accountId: selectedAccountId.value })
    accountsStore.fetchAccounts.execute()
  })
</script>

<template>
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
        <option value="">{{ tasksStore.fetchTasks.data?.total !== undefined ? `Все задачи (${tasksStore.fetchTasks.data?.total})` : 'Загрузка...' }}</option>
        <option value="failed">{{ tasksStore.fetchTasks.data?.statuses.failed !== undefined ? `C ошибками (${tasksStore.fetchTasks.data?.statuses.failed})` : 'Загрузка...' }}</option>
        <option value="queued">{{ tasksStore.fetchTasks.data?.statuses.queued !== undefined ? `В ожидании (${tasksStore.fetchTasks.data?.statuses.queued})` : 'Загрузка...' }}</option>
        <option value="done">{{ tasksStore.fetchTasks.data?.statuses.done !== undefined ? `Завершённые (${tasksStore.fetchTasks.data?.statuses.done})` : 'Загрузка...' }}</option>
      </select>

      <select class="form-select me-3" style="width: 280px" @change="filterByAccount" v-model="selectedAccountId">
        <option value="" :disabled="accountsStore.fetchAccounts.data?.length === 0">Все аккаунты</option>

        <option :value="selectedAccountId" v-if="accountsStore.fetchAccounts.data?.length === 0 && accountsStore.fetchAccounts.loading" disabled>Загрузка...</option>
        <option v-else v-for="account in accountsStore.fetchAccounts.data" :key="account.account_id" :value="account.account_id">
          {{ account.screen_name }} ({{ account.first_name }} {{ account.last_name }})
        </option>
      </select>

      <button class="btn btn-danger btn-action me-3"
        :disabled="tasksStore.fetchTasks.data?.tasks.length === 0"
        @click="showDeleteAllTasksModal"
      >
        Удалить задачи
      </button>

      <button class="btn btn-success btn-action"
        type="button"
        @click="showModal(AddTaskModal)"
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
                    <template v-if="tasksStore.fetchTasks.loading && !tasksStore.fetchTasks.data?.tasks.length">
                      <span class="spinner-border" role="status" style="width: 12px; height: 12px;">
                        <span class="visually-hidden">Загрузка...</span>
                      </span>
                    </template>

                    <template v-else>
                      <span>{{ tasksStore.fetchTasks.data?.tasks.length }}</span>
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
              v-for="task in tasksStore.fetchTasks.data?.tasks"
              :task="task"
              :key="task.id"
            />

            <tr v-if="tasksStore.fetchTasks.loading" style="height: 55px;">
              <td colspan="7">
                <div class="spinner-border" role="status" style="position: relative; top: 3px;">
                  <span class="visually-hidden">Загрузка...</span>
                </div>
              </td>
            </tr>

            <tr v-if="!tasksStore.fetchTasks.data?.tasks && !tasksStore.fetchTasks.loading" class="pe-none">
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
