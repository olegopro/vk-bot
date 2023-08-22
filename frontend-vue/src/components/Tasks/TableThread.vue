<template>
    <tr>
        <th scope="row">{{ task.id }}</th>
        <td>
            {{ task.first_name }} {{ task.last_name }}
        </td>
        <td>
            <TaskStatus :type="task.status" />
        </td>

        <td>{{ task.attempt_count }}</td>

        <td>

            <button
                class="btn btn-primary button-style me-2"
                data-bs-target="#taskDetails"
                data-bs-toggle="modal"
                type="button"
                @click="taskDetails(task.id)"
            >
                <svg width="16" height="16">
                    <use xlink:href="#info"></use>
                </svg>
            </button>

            <button
                class="btn btn-danger button-style"
                data-bs-target="#deleteTask"
                data-bs-toggle="modal"
                type="button"
                @click="deleteTask(task.id)"
            >
                <svg width="16" height="16">
                    <use xlink:href="#delete"></use>
                </svg>
            </button>
        </td>

        <td>{{ dateFormat(task.run_at) }}</td>
        <td>{{ dateFormat(task.created_at) }}</td>
    </tr>
</template>

<script>
    import { mapActions } from 'vuex'
    import TaskStatus from './TaskStatus.vue'

    export default {
        components: { TaskStatus },

        props: ['task'],
        emits: ['delete-task', 'task-details'],

        methods: {
            ...mapActions('tasks', ['accountByTaskId']),
            ...mapActions('account', ['getScreenNameById']),

            dateFormat(date) {
                const dt = new Date(date)
                return dt.getFullYear() + '-' +
                    String(dt.getMonth() + 1).padStart(2, '0') + '-' +
                    String(dt.getDate()).padStart(2, '0') + ' ' +
                    String(dt.getHours()).padStart(2, '0') + ':' +
                    String(dt.getMinutes()).padStart(2, '0') + ':' +
                    String(dt.getSeconds()).padStart(2, '0')
            },

            deleteTask(id) {
                this.$emit('delete-task', id)
            },

            taskDetails(id) {
                this.$emit('task-details', id)
            }
        }
    }
</script>

<style scoped lang="scss">
    .account-link {
        cursor: pointer;
    }
</style>
