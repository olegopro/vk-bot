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
            <router-link custom to="/" v-slot="{navigate}">
                <a class="btn btn-primary me-2 button-style" @click="navigate">
                    <svg width="16" height="16">
                        <use xlink:href="#info"></use>
                    </svg>
                </a>
            </router-link>

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

        <td>{{ dateFormat(task.created_at) }}</td>
    </tr>
</template>

<script>
    import { mapActions } from 'vuex'
    import TaskStatus from './TaskStatus.vue'

    export default {
        components: { TaskStatus },

        props: ['task'],
        emits: ['delete-task'],

        methods: {
            ...mapActions('tasks', ['accountByTaskId']),
            ...mapActions('account', ['getScreenNameById']),

            dateFormat(date) {
                return new Date(date).toISOString()
                    .replace('T', ' ')
                    .replace('Z', '')
                    .split('.')[0]
            },

            deleteTask(id) {
                this.$emit('delete-task', id)
            }
        }
    }
</script>

<style scoped lang="scss">
    .account-link {
        cursor: pointer;
    }
</style>
