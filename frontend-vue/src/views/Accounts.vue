<template>

    <div class="row mb-3 align-items-center">
        <div class="col d-flex align-items-center">
            <h1 class="h2 mb-0 me-3">Список аккаунтов</h1>

            <h6 class="mb-0" style="pointer-events: none">
               <span class="badge btn btn-secondary d-flex items-center fw-bold"
                     style="padding: 8px
               ">
                    <template v-if="accountsStore.isLoading && !accountsStore.accounts.length">
                       <span class="spinner-border" role="status" style="width: 12px; height: 12px;">
                          <span class="visually-hidden">Загрузка...</span>
                        </span>
                    </template>

                     <template v-else>
                        <span class="me-1">{{ accountsStore.pagination.total }}</span> / <span class="ms-1">{{ accountsStore.accounts.length }}</span>
                    </template>
                </span>
            </h6>
        </div>

        <div class="col">
            <button class="btn btn-success btn-action float-end"
                    type="button"
                    @click="showAddAccountModal"
            >
                Добавить аккаунт
            </button>
        </div>

    </div>

    <div class="row">
        <div class="col-12">
            <PerfectScrollbar class="ps-table">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 110px;">ID</th>
                            <th scope="col" style="width: 400px;">Имя и фамилия</th>
                            <th scope="col" style="width: 250px;">Логин</th>
                            <th scope="col" style="width: 250px;">Действия</th>
                            <th scope="col">День рождения</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="account in accountsStore.accounts" :key="account.account_id">
                            <th scope="row">{{ account.account_id }}</th>
                            <td>{{ account.first_name }} {{ account.last_name }}</td>
                            <td>{{ account.screen_name }}</td>

                            <td>
                                <router-link custom :to="{name: 'Account', params: {id: account.account_id}}" v-slot="{navigate}">
                                    <a class="btn btn-primary me-2 button-style" @click="navigate">
                                        <i class="bi bi-info-circle" />
                                    </a>
                                </router-link>

                                <button
                                    class="btn btn-danger button-style"
                                    type="button"
                                    @click="showDeleteAccountModal(account.screen_name, account.account_id)"
                                >
                                    <i class="bi bi-trash3" />
                                </button>
                            </td>

                            <td>{{ account.bdate }}</td>
                        </tr>

                        <tr v-if="accountsStore.isLoading" style="height: 55px;">
                            <td colspan="7">
                                <div class="spinner-border" role="status" style="position: relative; top: 3px;">
                                    <span class="visually-hidden">Загрузка...</span>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="accountsStore.accounts.length === 0 && !accountsStore.isLoading" class="pe-none">
                            <td colspan="7" style="height: 55px;">
                                Список аккаунтов пуст
                            </td>
                        </tr>

                        <tr class="load-more-trigger">
                            <td colspan="5" class="visually-hidden">
                                <span>Загрузка...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </PerfectScrollbar>
        </div>
    </div>

    <Teleport to="body">
        <component v-if="isOpen"
                   @mounted="showModal"
                   :is="modalComponent"
                   :login="selectedAccount.login"
                   :accountId="selectedAccount.id"
        />
    </Teleport>

</template>

<script setup>
    import { useAccountsStore } from '@/stores/AccountsStore'
    import AddAccount from '../components/Accounts/Modals/AddAccount.vue'
    import DeleteAccount from '../components/Accounts/Modals/DeleteAccount.vue'
    import { computed, onMounted, onUnmounted, provide, ref, shallowRef } from 'vue'
    import { debounce } from 'lodash'
    import { useModal } from '@/composables/useModal.ts'

    const { isOpen, preparedModal, showModal, closeModal } = useModal()

    const accountsStore = useAccountsStore()
    const selectedAccount = ref({ login: '', id: '' })
    const modalComponent = shallowRef(null)
    const observer = ref(null)
    const currentPage = ref(1)

    provide('closeModal', closeModal)

    const debouncedFetchAccounts = debounce((page) => {
        accountsStore.isLoading = true
        accountsStore.fetchAccounts(page).then(() => currentPage.value++)
    }, 500, {
        'leading': true, // Вызываться в начале периода ожидания
        'trailing': false // Дополнительный вызов в конце периода не требуется
    })

    const showAddAccountModal = () => {
        modalComponent.value = preparedModal(AddAccount)
        showModal('addAccountModal')
    }

    const showDeleteAccountModal = (login, id) => {
        selectedAccount.value = { login, id }
        modalComponent.value = preparedModal(DeleteAccount)
        showModal('deleteAccountModal')
    }

    onMounted(() => {
        console.log('Accounts onMounted')
        accountsStore.accounts = []
        debouncedFetchAccounts(currentPage.value)

        // Устанавливаем observer
        observer.value = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (
                    entry.isIntersecting &&
                    accountsStore.accounts.length !== accountsStore.pagination.total
                ) {
                    debouncedFetchAccounts(currentPage.value)
                }
            })
        })

        // Стартуем наблюдение за элементом с классом '.load-more-trigger'
        const loadMoreTrigger = document.querySelector('.load-more-trigger')
        if (loadMoreTrigger) observer.value.observe(loadMoreTrigger)
    })

    onUnmounted(() => {
        console.log('Accounts onUnmounted')
        if (observer.value) observer.value.disconnect() // Очищаем observer при размонтировании
    })
</script>
