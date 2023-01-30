<template>

    <div class="row mb-3">
        <div class="col">
            <h1 class="h2">Список задач</h1>
        </div>
        <div class="col">
            <button class="btn btn-success float-end"
                    data-bs-target="#addTask"
                    data-bs-toggle="modal"
                    type="button">
                Добавить задачу
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <table v-if="getTasks.length" class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Имя аккаунта</th>
                        <th scope="col">Статус</th>
                        <th scope="col">Попытки</th>
                        <th scope="col">Действия</th>
                        <th scope="col">Задача создана</th>
                    </tr>
                </thead>
                <tbody>
                    <TableThread v-for="task in getTasks" :task="task" :key="task.id" />
                </tbody>
            </table>

            <h3 v-else class="text-center">Список задач пустой</h3>

        </div>
    </div>

    <Teleport to="body">
        <DeleteTask />
        <AddTask />
    </Teleport>

</template>

<script>
    import { mapActions, mapGetters } from 'vuex'
    import TableThread from '../components/Tasks/TableThread.vue'
    import DeleteTask from '../components/Tasks/Modals/DeleteTask.vue'
    import AddTask from '../components/Tasks/Modals/AddTask.vue'

    export default {
        components: { AddTask, DeleteTask, TableThread },

        data() {
            return {
                request: {},
                username: ''
            }
        },

        mounted() {
            this.tasks()
        },

        computed: {
            ...mapGetters('tasks', ['getTasks'])
        },

        methods: {
            ...mapActions('tasks', ['tasks'])
        }
    }
</script>

<style scoped>

</style>
