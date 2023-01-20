<template>
    <table class="table table-hover">
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

    <Teleport to="body">
        <DeleteTask />
    </Teleport>

</template>

<script>
    import { mapActions, mapGetters } from 'vuex'
    import TableThread from '../components/Tasks/TableThread.vue'
    import DeleteTask from '../components/Tasks/Modals/DeleteTask.vue'

    export default {
        components: { DeleteTask, TableThread },

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
