<template>

    <div class="row mb-3 align-items-center">
        <div class="col">
            <h1 class="h2">Список аккаунтов</h1>
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
            <PerfectScrollbar>
                <table class="table table-hover" v-if="accountsStore.accounts.length">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Имя и фамилия</th>
                            <th scope="col">Логин</th>
                            <th scope="col">Действия</th>
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
                                        <svg width="16" height="16">
                                            <use xlink:href="#info"></use>
                                        </svg>
                                    </a>
                                </router-link>

                                <button
                                    class="btn btn-danger button-style"
                                    data-bs-target="#deleteAccount"
                                    data-bs-toggle="modal"
                                    type="button"
                                    @click="getLogin(account.screen_name, account.account_id)"
                                >
                                    <svg width="16" height="16">
                                        <use xlink:href="#delete"></use>
                                    </svg>
                                </button>
                            </td>

                            <td>{{ account.bdate }}</td>
                        </tr>
                    </tbody>
                </table>
                <h3 v-else class="text-center">Список аккаунтов пустой</h3>
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
    import { onMounted, ref } from 'vue'

    const accountsStore = useAccountsStore()
    const selectedAccount = ref({ login: '', id: '' })

    const getLogin = (login, id) => {
        selectedAccount.value = { login, id }
    }

    onMounted(() => accountsStore.fetchAccounts())
</script>

<style lang="scss" scoped>
    .ps {
        height: 80vh;
        box-shadow: var(--ps-shadow-box);
        border-radius: var(--ps-border-radius);
    }
</style>
