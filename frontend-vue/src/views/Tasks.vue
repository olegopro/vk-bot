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
                    @click="showAddTaskModal"
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
                                {{ tasksStore.totalTasksCount ? tasksStore.totalTasksCount : 0 }} / {{tasksStore.tasks.length}}
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
                            :showTaskDetailsModal="showTaskDetailsModal"
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

                        <tr v-if="tasksStore.tasks.length === 0 && !tasksStore.isLoading">
                            <td colspan="7" style="height: 55px;">
                                Список задач пуст
                            </td>
                        </tr>

                        <tr class="load-more-trigger visually-hidden">
                            <td colspan="7" style="height: 55px;">
                                <span>Загрузка...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </PerfectScrollbar>
        </div>
    </div>

    <Teleport to="body">
        <AddTask :modalInstance="addTasksModal" />
        <TaskDetails :modalInstance="taskDetailsModal" :taskData="taskDetailsData" />
        <AccountDetails :modalInstance="accountDetailsModal" :accountData="accountDetailsData" />
        <DeleteTask :modalInstance="deleteTaskModal" :taskId="taskId" />
        <DeleteAllTasks :modalInstance="deleteAllTasksModal" :selectedTasksStatus="currentStatus" :selectedAccountId="selectedAccountId" />
    </Teleport>

</template>

<script setup>
    import { useRoute, useRouter } from 'vue-router'
    import { onMounted, onUnmounted, ref, watch } from 'vue'
    import { useTasksStore } from '@/stores/TasksStore'
    import { useAccountStore } from '@/stores/AccountStore'
    import { useAccountsStore } from '@/stores/AccountsStore'
    import { showErrorNotification } from '@/helpers/notyfHelper'
    import { Modal } from 'bootstrap'
    import { debounce } from 'lodash'
    import AddTask from '../components/Tasks/Modals/AddTask.vue'
    import TableThread from '../components/Tasks/TableThread.vue'
    import AccountDetails from '../components/Tasks/Modals/AccountDetails.vue'
    import TaskDetails from '../components/Tasks/Modals/TaskDetails.vue'
    import DeleteTask from '../components/Tasks/Modals/DeleteTask.vue'
    import DeleteAllTasks from '../components/Tasks/Modals/DeleteAllTasks.vue'

    const taskDetailsModal = ref(null)
    const deleteTaskModal = ref(null)
    const deleteAllTasksModal = ref(null)
    const accountDetailsModal = ref(null)
    const addTasksModal = ref(null)

    const tasksStore = useTasksStore()
    const accountsStore = useAccountsStore()
    const accountStore = useAccountStore()
    const route = useRoute()
    const router = useRouter()
    const observer = ref(null)
    const perfectScrollbarRef = ref(null)

    const taskId = ref(0)
    const currentPage = ref(1)

    const currentStatus = ref(route.params.status || '')
    const selectedAccountId = ref(route.params.accountId || '')

    const accountDetailsData = ref(null)
    const taskDetailsData = ref(null)

    watch(() => tasksStore.tasks.length, newLength => {
        if (newLength > 0 && !observer.value) {
            observer.value = new IntersectionObserver(entries =>
                entries.forEach(entry => {
                    if (
                        entry.isIntersecting &&
                        tasksStore.totalTasksCount !== tasksStore.tasks.length
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
        'leading': true, // Вызываться в начале периода ожидания
        'trailing': false // Дополнительный вызов в конце периода не требуется
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

    const showAccountDetailsModal = (accountId, ownerId) => {
        accountDetailsData.value = null
        accountDetailsModal.value.show()

        accountStore.fetchOwnerData(accountId, ownerId)
            .then(() => {
                const ownerData = accountStore.getOwnerDataById(ownerId)
                accountDetailsData.value = { ...ownerData }
            })
            .catch(error => showErrorNotification(error))
    }

    const showTaskDetailsModal = async (newTaskId) => {
        taskDetailsData.value = null // Очищаем предыдущие данные
        taskDetailsModal.value.show() // Показываем модальное окно

        const response = await tasksStore.taskDetails(newTaskId)
        taskDetailsData.value = { ...response, taskId: newTaskId } // Добавляем taskId в response
    }

    const showDeleteTaskModal = (id) => {
        taskId.value = id
        deleteTaskModal.value.show()
    }

    const showDeleteAllTasksModal = () => {
        tasksStore.getTasksCountByStatus(currentStatus.value, selectedAccountId.value)
        deleteAllTasksModal.value.show()
    }
    const showAddTaskModal = () => addTasksModal.value.show()

    onMounted(() => {
        console.log('Tasks onMounted')

        tasksStore.tasks = []
        debouncedFetchTasks(currentStatus.value, selectedAccountId.value, currentPage.value)

        processRouteParams()
        accountsStore.fetchAccounts()

        deleteTaskModal.value = new Modal(document.getElementById('deleteTask'))
        taskDetailsModal.value = new Modal(document.getElementById('taskDetails'))
        deleteAllTasksModal.value = new Modal(document.getElementById('deleteAllTasks'))
        accountDetailsModal.value = new Modal(document.getElementById('accountDetails'))
        addTasksModal.value = new Modal(document.getElementById('addTask'))
    })

    onUnmounted(() => {
        console.log('Tasks onUnmounted')
        if (observer.value) observer.value.disconnect()
    })
</script>
