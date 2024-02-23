<template>
    <div class="row mb-3 align-items-center">
        <div class="col d-flex align-items-center">
            <h1 class="h2 mb-0">Список задач</h1>
            <button class="btn btn-sm btn-secondary btn-action my-0 ms-3"
                    :disabled="cyclicTasksStore.cyclicTasks.length === 0"
                    @click="router.push({ name: 'Tasks' })"
            >
                <b>Циклические задачи</b>
            </button>
        </div>
    </div>

    <div class="col d-flex justify-content-end">
    </div>

    <div class="row">
        <div class="col-12">
            <table v-if="cyclicTasksStore.cyclicTasks.length" class="table table-hover mb-4">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Аккаунт</th>
                        <th scope="col">Количество</th>
                        <th scope="col">Осталось</th>
                        <th scope="col">Задач в час</th>
                        <th scope="col">Статус задачи</th>
                        <th scope="col">Действия</th>
                        <th scope="col">Старт задачи</th>
                        <th scope="col">Задача создана</th>
                    </tr>
                </thead>

                <tbody>
                    <TableThread
                        v-for="cyclicTask in cyclicTasksStore.cyclicTasks"
                        :cyclicTask="cyclicTask"
                        :key="cyclicTask.id"
                    />
                </tbody>
            </table>

            <h3 v-else class="text-center">Список задач пустой</h3>

        </div>
    </div>

    <!--<div v-for="cyclicTask in cyclicTasksStore.cyclicTasks" :key="cyclicTask.id">
        {{cyclicTask}}
    </div>-->
</template>

<script setup>
    import { useCyclicTasksStore } from '../stores/CyclicTasksStore'
    import TableThread from '../components/CyclicTasks/TableThread.vue'
    import { onMounted } from 'vue'
    import router from '../router'

    const cyclicTasksStore = useCyclicTasksStore()

    onMounted(() => {
        cyclicTasksStore.fetchCyclicTasks()
    })
</script>
