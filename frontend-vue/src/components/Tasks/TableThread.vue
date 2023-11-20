<template>
    <tr>
        <th scope="row">{{ task.id }}</th>
        <td class="user-name inner-shadow">
            <div class="flex-container"
                 @click="accountDetails(task.owner_id)"
                 data-bs-target="#accountDetails"
                 data-bs-toggle="modal"
            >
                {{ task.first_name }} {{ task.last_name }}
                <i class="bi bi-person-circle ms-2" style="font-size: 16px; opacity: 0.75"/>
            </div>
        </td>

        <td>
            <TaskStatus :type="task.status" :errorMessage="task.error_message"/>
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
                <i class="bi bi-trash3" />
            </button>
        </td>

        <td>{{ dateFormat(task.run_at) }}</td>
        <td>{{ dateFormat(task.created_at) }}</td>
    </tr>
</template>

<script setup>
    import { defineProps, defineEmits, toRefs } from 'vue'
    import TaskStatus from './TaskStatus.vue'
    import { format } from 'date-fns'

    const props = defineProps(['task'])
    const emit = defineEmits(['delete-task', 'task-details', 'account-details'])

    const { task } = toRefs(props)

    const dateFormat = (date) => {
        return format(new Date(date), 'yyyy-MM-dd HH:mm:ss')
    }

    const deleteTask = (id) => {
        emit('delete-task', id)
    }

    const taskDetails = (id) => {
        emit('task-details', id)
    }

    const accountDetails = (ownerId) => {
        emit('account-details', ownerId)
    }
</script>

<style scoped lang="scss">
    .flex-container {
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        width: fit-content;
        margin-left: auto;
        margin-right: auto;
    }
</style>
