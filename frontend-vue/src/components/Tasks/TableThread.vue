<template>
    <tr>
        <th scope="row">{{ task.id }}</th>
        <td class="user-name inner-shadow">
            <div class="flex-container" @click="showAccountDetailsModal(task.account_id, task.owner_id)" >
                {{ task.first_name }} {{ task.last_name }}
                <i class="bi bi-person-circle ms-2" style="font-size: 16px; opacity: 0.75" />
            </div>
        </td>

        <td>
            <TaskStatus :type="task.status" :errorMessage="task.error_message" />
        </td>

        <td>
            <button
                class="btn btn-primary button-style me-2"
                type="button"
                @click="showTaskDetailsModal(task.id)"
            >
                <svg width="16" height="16">
                    <use xlink:href="#info"></use>
                </svg>
            </button>

            <button class="btn btn-danger button-style" @click="props.showDeleteTaskModal(task.id)">
                <i class="bi bi-trash3" />
            </button>
        </td>

        <td>{{ dateFormat(task.run_at) }}</td>
        <td>{{ dateFormat(task.created_at) }}</td>
    </tr>
</template>

<script setup>
    import { defineProps, toRefs } from 'vue'
    import TaskStatus from './TaskStatus.vue'
    import { format } from 'date-fns'

    const props = defineProps({
        task: Object,
        showTaskDetailsModal: Function,
        showDeleteTaskModal: Function,
        showAccountDetailsModal: Function
    })

    const { task } = toRefs(props)

    const dateFormat = (date) => format(new Date(date), 'yyyy-MM-dd HH:mm:ss')
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
