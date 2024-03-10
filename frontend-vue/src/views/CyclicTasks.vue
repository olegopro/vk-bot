<template>
    <div class="row mb-3 align-items-center">
        <div class="col d-flex align-items-center">
            <h1 class="h2 mb-0">
                Список задач
            </h1>
            <button class="btn btn-sm btn-primary btn-action my-0 ms-3 me-3"
                    @click="router.push({ name: 'Tasks' })"
            >
                <b>Циклические задачи</b>
            </button
            >

            <h6 class="mb-0" style="pointer-events: none">
                <span class="badge btn d-flex items-center fw-bold"
                      :class="{
                        'btn-success ': isTotalMatched,
                        'text-bg-secondary' : !isTotalMatched
                     }"
                      style="padding: 8px
               ">
                    <template v-if="cyclicTasksStore.isLoading && !cyclicTasksStore.cyclicTasks.length">
                       <span class="spinner-border" role="status" style="width: 12px; height: 12px;">
                          <span class="visually-hidden">Загрузка...</span>
                        </span>
                    </template>

                    <template v-else>
                        <span class="fw-bolder me-1">{{ cyclicTasksStore.pagination.total }}</span> / <span class="ms-1" :class="{'fw-bolder': isTotalMatched}">{{ cyclicTasksStore.cyclicTasks.length }}</span>
                    </template>
                </span>
            </h6>
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
                <table v-if="cyclicTasksStore.cyclicTasks" class="table table-hover">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 110px;">#</th>
                            <th scope="col" style="width: 140px;">Аккаунт</th>
                            <th scope="col" style="width: 106px;">Количество</th>
                            <th scope="col" style="width: 106px;">Осталось</th>
                            <th scope="col" style="width: 108px;">Задач в час</th>
                            <th scope="col" style="width: 130px;">Статус задачи</th>
                            <th scope="col" style="width: 150px;">Действия</th>
                            <th scope="col" style="width: 170px">Старт задачи</th>
                            <th scope="col" style="width: 170px">Задача создана</th>
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

                        <tr v-if="cyclicTasksStore.isLoading" style="height: 55px;">
                            <td colspan="9">
                                <div class="spinner-border" role="status" style="position: relative; top: 3px;">
                                    <span class="visually-hidden">Загрузка...</span>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="cyclicTasksStore.cyclicTasks.length === 0 && !cyclicTasksStore.isLoading && currentPage === 1">
                            <td colspan="9" style="height: 55px;">
                                Список циклических задач пуст
                            </td>
                        </tr>
                        <tr class="load-more-trigger visually-hidden">
                            <td colspan="9" style="height: 55px;">
                                <span>Загрузка...</span>
                            </td>
                        </tr>
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
    import { nextTick, onMounted, ref, provide, shallowRef, computed } from 'vue'
    import { Modal } from 'bootstrap'
    import { useCyclicTasksStore } from '../stores/CyclicTasksStore'
    import { useAccountsStore } from '../stores/AccountsStore'
    import TableThread from '../components/CyclicTasks/TableThread.vue'
    import DeleteCyclicTask from '../components/CyclicTasks/Modals/DeleteCyclicTask.vue'
    import AddCyclicTask from '../components/CyclicTasks/Modals/AddCyclicTask.vue'
    import EditCyclicTask from '../components/CyclicTasks/Modals/EditCyclicTask.vue'
    import DeleteAllCyclicTasks from '../components/CyclicTasks/Modals/DeleteAllCyclicTasks.vue'
    import router from '../router'
    import { debounce } from 'lodash'

    const taskId = ref(null)
    const modalComponent = shallowRef(null)
    const modals = ref({})
    const observer = ref(null)
    const currentPage = ref(1)

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

    const isTotalMatched = computed(() => {
        return cyclicTasksStore.pagination.total === cyclicTasksStore.cyclicTasks.length
    })

    const debouncedFetchCyclicTasks = debounce((page) => {
        cyclicTasksStore.isLoading = true
        cyclicTasksStore.fetchCyclicTasks(page).then(() => currentPage.value++)
    }, 500, {
        'leading': true, // Вызываться в начале периода ожидания
        'trailing': false // Дополнительный вызов в конце периода не требуется
    })

    onMounted(() => {
        cyclicTasksStore.cyclicTasks = []

        debouncedFetchCyclicTasks()
        accountsStore.fetchAccounts()

        accountsStore.accounts = []
        // Устанавливаем observer
        observer.value = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                console.log(' observer.value = new IntersectionObserver(entries => {')
                if (
                    entry.isIntersecting &&
                    cyclicTasksStore.cyclicTasks.length !== cyclicTasksStore.pagination.total
                ) {
                    debouncedFetchCyclicTasks(currentPage.value)
                }
            })
        })

        // Стартуем наблюдение за элементом с классом '.load-more-trigger'
        const loadMoreTrigger = document.querySelector('.load-more-trigger')
        if (loadMoreTrigger) observer.value.observe(loadMoreTrigger)
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
