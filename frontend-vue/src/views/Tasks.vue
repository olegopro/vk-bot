<script setup lang="ts">
  import { useRoute, useRouter } from 'vue-router'
  import { onMounted, onUnmounted, ref, watch, computed } from 'vue'
  import { useTasksStore } from '@/stores/TasksStore'
  import { useAccountStore } from '@/stores/AccountStore'
  import { useAccountsStore } from '@/stores/AccountsStore'
  import { showErrorNotification } from '@/helpers/notyfHelper'
  import { useModal } from '@/composables/useModal'
  import { debounce } from 'lodash'
  import AddTask from '../components/Tasks/Modals/AddTask.vue'
  import TableThread from '../components/Tasks/TableThread.vue'
  import AccountDetailsModal from '../components/Tasks/Modals/AccountDetailsModal.vue'
  import DeleteTaskModal from '../components/Tasks/Modals/DeleteTaskModal.vue'
  import DeleteAllTasksModal from '../components/Tasks/Modals/DeleteAllTasksModal.vue'

  const tasksStore = useTasksStore()
  const accountsStore = useAccountsStore()
  const accountStore = useAccountStore()
  const route = useRoute()
  const router = useRouter()
  const observer = ref(null)
  const perfectScrollbarRef = ref(null)
  const { showModal } = useModal()

  const taskId = ref(0)
  const currentPage = ref(1)

  const currentStatus = ref<string>(route.params.status as string || '')
  const selectedAccountId = ref<string>(route.params.accountId as string || '')

  const accountDetailsData = ref<object>({})

  watch(() => tasksStore.tasks.length, newLength => {
    if (newLength > 0 && !observer.value) {
      observer.value = new IntersectionObserver(entries =>
        entries.forEach(entry => {
          if (
            entry.isIntersecting &&
            totalTasksByStatus.value !== tasksStore.tasks.length
          ) {
            debouncedFetchTasks(currentStatus.value, selectedAccountId.value, currentPage.value)
          }
        }))

      observer.value.observe(document.querySelector('.load-more-trigger'))
    }
  }, { immediate: false })

  // Следим за изменениями статуса и ID аккаунта
  watch([currentStatus, selectedAccountId], () => {
    // Сброс currentPage на 1 перед загрузкой новых данных
    debouncedFetchTasks(currentStatus.value, selectedAccountId.value, currentPage.value = 1)
  })

  watch(route, () => {
    tasksStore.tasks = []
    processRouteParams()
    perfectScrollbarRef.value.$el.scrollTop = 0
  })

  const debouncedFetchTasks = debounce((status, accountId, page = 1) => {
    tasksStore.fetchTasks(status, accountId, page).then(() => currentPage.value++)
  }, 500, {
    leading: true, // Вызываться в начале периода ожидания
    trailing: false // Дополнительный вызов в конце периода не требуется
  })

  const processRouteParams = () => {
    // Получаем текущие параметры маршрута из vue-router.
    const params = route.params

    // Проверяем наличие параметра status в URL.
    if (params.status) {
      // Если параметр status присутствует, проверяем, является ли он числом и больше ли он нуля.
      // Эта проверка необходима, чтобы определить, относится ли параметр к идентификатору аккаунта.
      if (!isNaN(params.status) && parseInt(params.status) > 0) {
        // Если условие выполняется, значит, параметр status является идентификатором аккаунта.
        // Устанавливаем его значение в selectedAccountId компонента.
        selectedAccountId.value = params.status
        // Так как status использовался для accountId, сбрасываем currentStatus.
        currentStatus.value = '' // Сброс, указывая, что это ID аккаунта, а не статус.
      } else {
        // Если status не является числом или не больше нуля, то предполагаем, что это статус задачи.
        // Устанавливаем его значение в currentStatus компонента.
        currentStatus.value = params.status
      }
    }

    // Проверяем, есть ли параметр accountId в URL.
    // Это дополнительная проверка для случаев, когда параметр accountId явно передан в URL.
    if (params.accountId) {
      // Если параметр accountId присутствует, устанавливаем его значение в selectedAccountId компонента.
      // Это позволяет явно выбрать аккаунт, не полагаясь на проверку status.
      selectedAccountId.value = params.accountId
    }
  }

  const filterTasks = (event) => {
    const status = event.target.value || ''
    const accountId = selectedAccountId.value || ''
    router.push({ name: 'Tasks', params: { status, accountId } })
    debouncedFetchTasks(status, accountId)
  }

  const filterByAccount = () => {
    const status = currentStatus.value || ''
    const accountId = selectedAccountId.value || ''
    router.push({ name: 'Tasks', params: { status, accountId } })
    debouncedFetchTasks(status, accountId)
  }

  const totalTasksByStatus = computed(() => {
    // текущий статус задачи, полученный из параметров роута
    const status = currentStatus.value

    // Возвращаем значение в зависимости от статуса
    switch (status) {
      case 'queued':
        return tasksStore.totalTasksQueued

      case 'failed':
        return tasksStore.totalTasksFailed

      case 'done':
        return tasksStore.totalTasksDone

      default:
        return tasksStore.totalTasksCount
    }
  })

  const showAccountDetailsModal = (accountId: number, ownerId: number, taskId: number | null) => {
    console.log(accountId)

    accountStore.fetchOwnerData(accountId, ownerId, taskId)
      .then(() => {
        const ownerData = accountStore.getOwnerDataById(ownerId)
        accountDetailsData.value = { ...ownerData }

        showModal(AccountDetailsModal, { accountData: accountDetailsData.value })
      })
      .catch(error => showErrorNotification(error.response.data.message))
  }

  const showDeleteTaskModal = (id) => {
    taskId.value = id
    modalComponent.value = preparedModal(DeleteTaskModal)
    showModal('deleteTaskModal')
  }

  const showDeleteAllTasksModal = () => {
    showModal(DeleteAllTasksModal, {
      selectedTasksStatus: currentStatus.value,
      selectedAccountId: selectedAccountId.value
    })

    tasksStore.getTasksCountByStatus(currentStatus.value, selectedAccountId.value)
  }

  onMounted(() => {
    tasksStore.tasks = []
    debouncedFetchTasks(currentStatus.value, selectedAccountId.value, currentPage.value)

    processRouteParams()
    accountsStore.fetchAccounts.execute()
  })

  onUnmounted(() => {
    if (observer.value) observer.value.disconnect()
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
        <option value="">{{ tasksStore.totalTasksCount !== null ? `Все задачи (${tasksStore.totalTasksCount})` : 'Загрузка...' }}</option>
        <option value="failed">{{ tasksStore.totalTasksFailed !== null ? `C ошибками (${tasksStore.totalTasksFailed})` : 'Загрузка...' }}</option>
        <option value="queued">{{ tasksStore.totalTasksQueued !== null ? `В ожидании (${tasksStore.totalTasksQueued})` : 'Загрузка...' }}</option>
        <option value="done">{{ tasksStore.totalTasksDone !== null ? `Завершённые (${tasksStore.totalTasksDone})` : 'Загрузка...' }}</option>
      </select>

      <select class="form-select me-3" style="width: 280px" @change="filterByAccount" v-model="selectedAccountId">
        <option value="" :disabled="accountsStore.accounts.length === 0">Все аккаунты</option>

        <option :value="selectedAccountId" v-if="accountsStore.accounts.length === 0 && accountsStore.isLoading" disabled>Загрузка...</option>
        <option v-else v-for="account in accountsStore.accounts" :key="account.id" :value="account.account_id">
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
                      <span class="me-1">{{ totalTasksByStatus ? totalTasksByStatus : 0 }}</span> / <span class="ms-1">{{ tasksStore.tasks.length }}</span>
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
              :showDeleteTaskModal="showDeleteTaskModal"
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

            <tr class="load-more-trigger">
              <td colspan="7" class="visually-hidden">
                <span>Загрузка...</span>
              </td>
            </tr>
          </tbody>
        </table>
      </PerfectScrollbar>
    </div>
  </div>

  <!--<Teleport to="body">
    <component v-if="isOpen"
      @mounted="showModal"
      :is="modalComponent"
      :taskId="taskId"
      :selectedTasksStatus="currentStatus"
      :selectedAccountId="selectedAccountId"
      :taskData="taskDetailsData"
      :accountData="accountDetailsData"
    />
  </Teleport>-->

</template>
