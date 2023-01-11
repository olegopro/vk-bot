<template>
    <tr>
        <th scope="row">{{ task.id }}</th>
        <!--<td>{{ task.account_id }}</td>-->
        <td>{{ username }}</td>
        <td>{{ task.payload }}</td>
        <td>{{ task.attempts }}</td>
        <td>{{ task.created_at }}</td>
    </tr>
</template>

<script>

    import { mapActions } from 'vuex'

    export default {
        props: ['task'],

        data() {
            return {
                username: ''
            }
        },

        created() {
            this.accountByTaskId(this.task.account_id)
                .then(({ data }) => {
                    this.username = data.username
                })
        },

        methods: {
            ...mapActions('tasks', ['accountByTaskId'])
        }
    }
</script>

<style scoped>

</style>
