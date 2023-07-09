<template>

    <div class="row mb-3 align-items-center">
        <div class="col">
            <h1 class="h2">Список задач</h1>
        </div>
        <div class="col">

            <button class="btn btn-success btn-action float-end"
                    data-bs-target="#addTask"
                    data-bs-toggle="modal"
                    type="button">
                Добавить задачу
            </button>

            <button class="btn btn-danger btn-action float-end me-3"
                    v-if="getTasks.length > 0"
                    data-bs-target="#deleteAllTasks"
                    data-bs-toggle="modal"
                    type="button">
                Очистить список
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
                    <th scope="col">Задача создана</th>
                </tr>
                </thead>
                <tbody>

                <TableThread
                    v-for="task in getTasks"
                    :task="task"
                    :key="task.id"
                    @deleteTask="idTaskForDelete"
                />

                </tbody>
            </table>

            <h3 v-else class="text-center">Список задач пустой</h3>

        </div>
    </div>

    <Teleport to="body">
        <AddTask />
        <DeleteTask :taskId="taskId" />
        <DeleteAllTasks :tasksCount="getTasks"/>
    </Teleport>

</template>

<script>
    import { mapActions, mapGetters } from 'vuex'
    import TableThread from '../components/Tasks/TableThread.vue'
    import DeleteTask from '../components/Tasks/Modals/DeleteTask.vue'
    import AddTask from '../components/Tasks/Modals/AddTask.vue'
    import DeleteAllTasks from '../components/Tasks/Modals/DeleteAllTasks.vue'

    export default {
        components: { DeleteAllTasks, AddTask, DeleteTask, TableThread },

        data() {
            return {
                request: {},
                username: '',
                taskId: null
            }
        },

        mounted() {
            this.tasks()
        },

        computed: {
            ...mapGetters('tasks', ['getTasks'])
        },

        methods: {
            ...mapActions('tasks', ['tasks']),

            helloFromDeleteTask(data) {
                console.log(data)
            },

            idTaskForDelete(id) {
                this.taskId = id
            }
        }
    }
</script>

<style scoped lang="scss">

</style>
