<template>
    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Имя аккаунта</th>
                <th scope="col">Задача (payload)</th>
                <th scope="col">Попытки</th>
                <th scope="col">Задача создана</th>
            </tr>
        </thead>
        <tbody>
            <TableThread v-for="task in getTasks" :task="task" :key="task.id" />
        </tbody>
    </table>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex'
    import TableThread from '../components/Tasks/TableThread.vue'

    export default {
        components: { TableThread },

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
