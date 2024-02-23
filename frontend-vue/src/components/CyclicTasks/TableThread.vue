<template>
    <tr>
        <th scope="row">{{ cyclicTask.id }}</th>
        <td class="user-name inner-shadow">
            <div class="flex-container">
                {{ cyclicTask.account_id }}
                <i class="bi bi-person-circle ms-2" style="font-size: 16px; opacity: 0.75" />
            </div>
        </td>

        <td>
            {{ cyclicTask.tasks_count }}
        </td>

        <td>
            {{ cyclicTask.tasks_count }}
        </td>

        <td>
            {{ cyclicTask.tasks_per_hour }}
        </td>

        <td>
            <TaskStatus :type="cyclicTask.status" :errorMessage="cyclicTask.error_message" />
        </td>

        <td>
            <button
                class="btn btn-primary button-style me-2"
                type="button"
            >
                <svg width="16" height="16">
                    <use xlink:href="#info"></use>
                </svg>
            </button>

            <button class="btn btn-danger button-style" @click="props.showDeleteCyclicTaskModal(cyclicTask.id)">
                <i class="bi bi-trash3" />
            </button>
        </td>

        <td>{{ dateFormat(cyclicTask.started_at) }}</td>
        <td>{{ dateFormat(cyclicTask.created_at) }}</td>
    </tr>
</template>

<script setup>
    import { defineProps, toRefs } from 'vue'
    import TaskStatus from './TaskStatus.vue'
    import { format } from 'date-fns'

    const props = defineProps({
        cyclicTask: Object,
        showDeleteCyclicTaskModal: Function
    })

    const { cyclicTask } = toRefs(props)

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
