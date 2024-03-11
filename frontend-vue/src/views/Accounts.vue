<template>

    <div class="row mb-3 align-items-center">
        <div class="col d-flex align-items-center">
            <h1 class="h2 mb-0 me-3">Список аккаунтов</h1>

            <h6 class="mb-0" style="pointer-events: none">
               <span class="badge btn d-flex items-center fw-bold"
                     :class="{
                        'btn-success ': isTotalMatched,
                        'text-bg-secondary' : !isTotalMatched
                     }"
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
                    data-bs-target="#addAccount"
                    data-bs-toggle="modal"
                    type="button">
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
                                    data-bs-target="#deleteAccount"
                                    data-bs-toggle="modal"
                                    type="button"
                                    @click="getLogin(account.screen_name, account.account_id)"
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

                        <tr v-if="accountsStore.accounts.length === 0 && !accountsStore.isLoading && currentPage === 1">
                            <td colspan="7" style="height: 55px;">
                                Список аккаунтов пуст
                            </td>
                        </tr>
                        <tr class="load-more-trigger visually-hidden">
                            <td colspan="7" style="height: 55px;">
                                <span>Загрузка...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </PerfectScrollbar>
        </div>
    </div>

    <Teleport to="body">
        <DeleteAccount :login="selectedAccount.login" :id="selectedAccount.id" />
        <AddAccount />
    </Teleport>

</template>

<script setup>
    import { useAccountsStore } from '@/stores/AccountsStore'
    import DeleteAccount from '../components/Accounts/Modals/DeleteAccount.vue'
    import AddAccount from '../components/Accounts/Modals/AddAccount.vue'
    import { computed, onMounted, onUnmounted, ref } from 'vue'
    import { debounce } from 'lodash'

    const accountsStore = useAccountsStore()
    const selectedAccount = ref({ login: '', id: '' })
    const observer = ref(null)
    const currentPage = ref(0)

    const getLogin = (login, id) => {
        selectedAccount.value = { login, id }
    }

    const isTotalMatched = computed(() => {
        return accountsStore.pagination.total === accountsStore.accounts.length
    })

    const debouncedFetchAccounts = debounce((page) => {
        accountsStore.isLoading = true
        accountsStore.fetchAccounts(page)
    }, 500, {
        'leading': true, // Вызываться в начале периода ожидания
        'trailing': false // Дополнительный вызов в конце периода не требуется
    })

    onMounted(() => {
        console.log('Accounts onMounted')
        accountsStore.accounts = []
        // Устанавливаем observer
        observer.value = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                console.log(' observer.value = new IntersectionObserver(entries => {')
                if (
                    entry.isIntersecting &&
                    accountsStore.accounts.length !== accountsStore.pagination.total
                ) {
                    currentPage.value++
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
