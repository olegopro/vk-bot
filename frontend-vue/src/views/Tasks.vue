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
                    :disabled="getTasks.length === 0"
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
            <table v-if="getTasks.length" class="table table-hover mb-4">
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
                        v-for="task in getTasks"
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
        <DeleteTask :taskId="taskId" :taskData="taskDetailsData" />
        <DeleteAllTasks :tasksCount="getTasks" />
        <TaskDetails :taskData="taskDetailsData" :taskId="taskId" @deleteLike="deleteLikeTask" ref="TaskDetailsRef" />
        <AccountDetails :accountData="accountDetailsData" />
    </Teleport>

</template>

<script>
    import { mapActions, mapGetters } from 'vuex'
    import TableThread from '../components/Tasks/TableThread.vue'
    import DeleteTask from '../components/Tasks/Modals/DeleteTask.vue'
    import AddTask from '../components/Tasks/Modals/AddTask.vue'
    import DeleteAllTasks from '../components/Tasks/Modals/DeleteAllTasks.vue'
    import TaskDetails from '../components/Tasks/Modals/TaskDetails.vue'
    import AccountDetails from '../components/Tasks/Modals/AccountDetails.vue'

    export default {
        components: { TaskDetails, DeleteAllTasks, AddTask, DeleteTask, TableThread, AccountDetails },

        data() {
            return {
                request: {},
                username: '',
                taskId: null,
                taskDetailsData: null,
                accountDetailsData: null,
                currentStatus: ''
            }
        },

        mounted() {
            this.currentStatus = this.$route.params.status || ''
            this.tasks(this.currentStatus)
        },

        computed: {
            ...mapGetters('tasks', ['getTasks']),
            ...mapGetters('account', ['getOwnerDataById'])
        },

        methods: {
            ...mapActions('tasks', ['tasks', 'taskDetails', 'deleteLike']),
            ...mapActions('account', ['ownerData']),

            helloFromDeleteTask(data) {
                console.log(data)
            },

            getTaskId(id) {
                this.taskId = id
            },

            filterTasks(event) {
                const status = event.target.value
                this.$router.push({ name: 'Tasks', params: { status: status } })
                this.tasks(status)
            },

            async getTaskDetailsById(id) {
                this.taskDetailsData = null
                await this.getTaskId(id)

                this.taskDetails(this.taskId)
                    .then(({ data }) => {
                        this.taskDetailsData = data.response
                        this.$refs.TaskDetailsRef.disableSubmit = false
                    })
            },

            async deleteLikeTask(id) {
                await this.getTaskId(id)

                this.deleteLike(this.taskId)
                    .then(() => {
                        this.getTaskDetailsById(this.taskId)
                        this.$refs.TaskDetailsRef.disableSubmit = true
                    })
            },

            getAccountDetails(ownerId) {
                this.ownerData(ownerId)
                    .then(() => {
                        this.accountDetailsData = this.getOwnerDataById(ownerId)
                    })
            }
        }
    }
</script>

<style scoped lang="scss">

</style>
