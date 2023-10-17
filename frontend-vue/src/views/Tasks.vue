<template>
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h1 class="h2">Список задач</h1>
        </div>
        <div class="col d-flex justify-content-end">

            <select class="form-select me-3 w-auto" @change="filterTasks" v-model="currentStatus">
                <option value="">Все задачи</option>
                <option value="pending">В ожидании</option>
                <option value="done">Завершённые</option>
            </select>

            <button class="btn btn-danger btn-action me-3"
                    :disabled="tasksStore.getTasks.length === 0"
                    data-bs-target="#deleteAllTasks"
                    data-bs-toggle="modal"
                    type="button">
                Очистить список
            </button>

            <button class="btn btn-success btn-action"
                    data-bs-target="#addTask"
                    data-bs-toggle="modal"
                    type="button">
                Добавить задачу
            </button>

        </div>

    </div>

    <div class="row">
        <div class="col-12">
            <table v-if="tasksStore.getTasks.length" class="table table-hover mb-4">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Имя и фамилия</th>
                        <th scope="col">Статус</th>
                        <th scope="col">Попытки</th>
                        <th scope="col">Действия</th>
                        <th scope="col">Старт задачи</th>
                        <th scope="col">Задача создана</th>
                    </tr>
                </thead>
                <tbody>

                    <TableThread
                        v-for="task in tasksStore.getTasks"
                        :task="task"
                        :key="task.id"
                        @taskDetails="getTaskDetailsById"
                        @deleteTask="getTaskId"
                        @accountDetails="getAccountDetails"
                    />

                </tbody>
            </table>

            <h3 v-else class="text-center">Список задач пустой</h3>

        </div>
    </div>

    <Teleport to="body">
        <AddTask />
        <TaskDetails :taskData="taskDetailsData" :taskId="taskId" @deleteLike="deleteLikeTask" ref="TaskDetailsRef" />
        <AccountDetails :accountData="accountDetailsData" />
        <DeleteTask :taskId="taskId" :taskData="taskDetailsData" />
        <DeleteAllTasks :tasksCount="tasksStore.getTasks" />
    </Teleport>

</template>

<script setup>
    import { ref, onMounted } from 'vue'
    import { useTasksStore } from '@/stores/TasksStore'
    import { useAccountStore } from '../stores/AccountStore'
    import { useRoute, useRouter } from 'vue-router'
    import { showErrorNotification } from '../helpers/notyfHelper'
    import TableThread from '../components/Tasks/TableThread.vue'
    import AddTask from '../components/Tasks/Modals/AddTask.vue'
    import AccountDetails from '../components/Tasks/Modals/AccountDetails.vue'
    import TaskDetails from '../components/Tasks/Modals/TaskDetails.vue'
    import DeleteTask from '../components/Tasks/Modals/DeleteTask.vue'
    import DeleteAllTasks from '../components/Tasks/Modals/DeleteAllTasks.vue'

    const tasksStore = useTasksStore()
    const accountStore = useAccountStore()
    const route = useRoute()
    const router = useRouter()

    const taskId = ref(null)

    const currentStatus = ref(route.params.status || '')
    const accountDetailsData = ref(null)
    const taskDetailsData = ref(null)
    // const taskDetailsRef = ref(null)

    onMounted(() => {
        tasksStore.fetchTasks(currentStatus.value)
    })

    const filterTasks = (event) => {
        const status = event.target.value
        router.push({ name: 'Tasks', params: { status } })
        tasksStore.fetchTasks(status)
    }

    const getTaskId = (id) => {
        taskId.value = id
    }

    const getAccountDetails = (ownerId) => {
        console.log('ownerId', ownerId)
        accountStore.fetchOwnerData(ownerId)
            .then(() => {
                accountDetailsData.value = accountStore.getOwnerDataById(ownerId)
            })
            .catch(({ response }) => showErrorNotification(response.data.message))
    }

    const getTaskDetailsById = (id) => {
        taskDetailsData.value = null
        getTaskId(id)

        tasksStore.taskDetails(taskId.value)
            .then(({ data }) => {
                taskDetailsData.value = data.response
                // taskDetailsRef.value.disableSubmit = false
            })
    }

    const deleteLikeTask = (id) => {
        getTaskId(id)

        tasksStore.deleteLike(taskId.value)
            .then(() => {
                getTaskDetailsById(taskId.value)
                // this.$refs.TaskDetailsRef.disableSubmit = true
            })
    }

</script>
