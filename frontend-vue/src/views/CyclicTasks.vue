<template>
    <div class="row mb-3 align-items-center">
        <div class="col d-flex align-items-center">
            <h1 class="h2 mb-0">Список задач</h1>
            <button class="btn btn-sm btn-primary btn-action my-0 ms-3"
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

            <PerfectScrollbar>
                <table v-if="cyclicTasksStore.cyclicTasks.length" class="table table-hover">
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
            </PerfectScrollbar>

        </div>
    </div>

    <Teleport to="body">
        <component :is="modalComponent" :taskId="taskId" />
    </Teleport>

</template>

<script setup>
    import { nextTick, onMounted, ref, provide, shallowRef } from 'vue'
    import { Modal } from 'bootstrap'
    import { useCyclicTasksStore } from '../stores/CyclicTasksStore'
    import { useAccountsStore } from '../stores/AccountsStore'
    import TableThread from '../components/CyclicTasks/TableThread.vue'
    import DeleteCyclicTask from '../components/CyclicTasks/Modals/DeleteCyclicTask.vue'
    import AddCyclicTask from '../components/CyclicTasks/Modals/AddCyclicTask.vue'
    import EditCyclicTask from '../components/CyclicTasks/Modals/EditCyclicTask.vue'
    import DeleteAllCyclicTasks from '../components/CyclicTasks/Modals/DeleteAllCyclicTasks.vue'
    import router from '../router'

    const taskId = ref(null)
    const modalComponent = shallowRef(null)
    const modals = ref({})

    const cyclicTasksStore = useCyclicTasksStore()
    const accountsStore = useAccountsStore()

    provide('modals', modals)

    const openModal = (modalId, component) => {
        modalComponent.value = component

        nextTick(() => {
            if (!modals.value[modalId]) {
                const modalElement = document.getElementById(modalId)
                const modalInstance = new Modal(modalElement)

                modalElement.addEventListener('hidden.bs.modal', () => {
                    modalInstance.dispose()
                    delete modals.value[modalId]
                    modalComponent.value = null
                }, { once: true })

                modals.value[modalId] = modalInstance
            }

            modals.value[modalId].show()
        })
    }

    const showDeleteCyclicTaskModal = (id) => {
        taskId.value = id
        openModal('deleteCyclicTaskModal', DeleteCyclicTask)
    }

    const showEditCyclicTaskModal = (id) => {
        taskId.value = id
        openModal('editCyclicTaskModal', EditCyclicTask)
    }

    const showAddCyclicTaskModal = () => {
        openModal('addCyclicTaskModal', AddCyclicTask)
    }

    const showDeleteAllCyclicTasksModal = () => {
        openModal('deleteAllCyclicTasksModal', DeleteAllCyclicTasks)
    }

    onMounted(() => {
        cyclicTasksStore.fetchCyclicTasks()
        accountsStore.fetchAccounts()
    })
</script>

<style lang="scss" scoped>
    .ps {
        height: auto;
        max-height: var(--ps-height);
        box-shadow: var(--ps-shadow-box);
        border-radius: var(--ps-border-radius);
    }
</style>
