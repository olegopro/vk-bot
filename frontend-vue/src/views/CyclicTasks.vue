<template>
    <div class="row mb-3 align-items-center">
        <div class="col d-flex align-items-center">
            <h1 class="h2 mb-0">Список задач</h1>
            <button class="btn btn-sm btn-secondary btn-action my-0 ms-3"
                    @click="router.push({ name: 'Tasks' })"
            >
                <b>Циклические задачи</b>
            </button>
        </div>

        <div class="col d-flex justify-content-end">
            <button class="btn btn-danger btn-action me-3"
                    :disabled="cyclicTasksStore.cyclicTasks.length === 0"
                    @click="showDeleteAllCyclicTasksModal"
            >
                Очистить список
            </button>

            <button class="btn btn-success btn-action"
                    type="button"
                    @click="showAddCyclicTaskModal"
            >
                Добавить задачу
            </button>
        </div>
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
                        :showDeleteCyclicTaskModal="showDeleteCyclicTaskModal"
                        :showEditCyclicTaskModal="showEditCyclicTaskModal"
                    />
                </tbody>
            </table>

            <h3 v-else class="text-center">Список задач пустой</h3>

        </div>
    </div>

    <Teleport to="body">
        <DeleteCyclicTask :modalInstance="deleteCyclicTaskModal" :taskId="taskId"/>
        <AddCyclicTask :modalInstance="addCyclicTaskModal" />
        <DeleteAllCyclicTasks :modalInstance="deleteAllCyclicTasksModal" />
        <EditCyclicTask :modalInstance="editCyclicTaskModal" :taskId="taskId"/>
    </Teleport>

</template>

<script setup>
    import { useCyclicTasksStore } from '../stores/CyclicTasksStore'
    import TableThread from '../components/CyclicTasks/TableThread.vue'
    import { onMounted, ref } from 'vue'
    import router from '../router'
    import { Modal } from 'bootstrap'
    import DeleteCyclicTask from '../components/CyclicTasks/Modals/DeleteCyclicTask.vue'
    import AddCyclicTask from '../components/CyclicTasks/Modals/AddCyclicTask.vue'
    import { useAccountsStore } from '../stores/AccountsStore'
    import DeleteAllCyclicTasks from '../components/CyclicTasks/Modals/DeleteAllCyclicTasks.vue'
    import EditCyclicTask from '../components/CyclicTasks/Modals/EditCyclicTask.vue'

    const taskId = ref(null)
    const deleteCyclicTaskModal = ref(null)
    const addCyclicTaskModal = ref(null)
    const editCyclicTaskModal = ref(null)
    const deleteAllCyclicTasksModal = ref(null)

    const cyclicTasksStore = useCyclicTasksStore()
    const accountsStore = useAccountsStore()

    const showDeleteCyclicTaskModal = id => {
        taskId.value = id
        deleteCyclicTaskModal.value.show()
    }

    const showEditCyclicTaskModal = id => {
        taskId.value = id
        editCyclicTaskModal.value.show()
    }

    const showAddCyclicTaskModal = () => addCyclicTaskModal.value.show()
    const showDeleteAllCyclicTasksModal = () => deleteAllCyclicTasksModal.value.show()

    onMounted(() => {
        cyclicTasksStore.fetchCyclicTasks()
        accountsStore.fetchAccounts()

        deleteCyclicTaskModal.value = new Modal(document.getElementById('deleteCyclicTask'))
        addCyclicTaskModal.value = new Modal(document.getElementById('addCyclicTaskModal'))
        editCyclicTaskModal.value = new Modal(document.getElementById('editCyclicTaskModal'))
        deleteAllCyclicTasksModal.value = new Modal(document.getElementById('deleteAllCyclicTasks'))
    })
</script>
