<template>
    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">account_id</th>
                <th scope="col">payload</th>
                <th scope="col">attempts</th>
                <th scope="col">created_at</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="task in getTasks" :key="task.id">
                <th scope="row">{{ task.id }}</th>
                <td>{{ task.account_id }}</td>
                <td>{{ task.payload }}</td>
                <td>{{ task.attempts }}</td>
                <td>{{ task.created_at }}</td>
            </tr>
        </tbody>
    </table>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex'

    export default {
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
