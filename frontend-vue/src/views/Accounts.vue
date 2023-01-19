<template>
    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Имя и фамилия</th>
                <th scope="col">Логин</th>
                <th scope="col">Лайки</th>
                <th scope="col">Действия</th>
                <th scope="col">Добавлен</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="account in getAccounts" :key="account.id">
                <th scope="row">{{ account.id }}</th>
                <td>{{ account.username }}</td>
                <td>{{ account.login }}</td>
                <td>{{ account.likes_count }}</td>

                <td>
                    <router-link custom :to="{name: 'Account', params: {id: account.id}}" v-slot="{navigate}">
                        <a class="btn btn-primary me-2" @click="navigate">
                            <svg width="16" height="20">
                                <use xlink:href="#info"></use>
                            </svg>
                        </a>
                    </router-link>

                    <button
                        class="btn btn-danger"
                        data-bs-target="#deleteAccount"
                        data-bs-toggle="modal"
                        type="button"
                        @click="getLogin(account.login)"
                    >
                        <svg width="16" height="20">
                            <use xlink:href="#delete"></use>
                        </svg>
                    </button>
                </td>

                <td>{{ account.created_at }}</td>
            </tr>
        </tbody>
    </table>

    <Teleport to="body">
        <DeleteAccount  ref="popup"/>
    </Teleport>

</template>

<script>
    import { mapActions, mapGetters } from 'vuex'
    import DeleteAccount from '../components/Accounts/Modals/DeleteAccount.vue'

    export default {
        components: { DeleteAccount },

        computed: {
            ...mapGetters('accounts', ['getAccounts'])
        },

        mounted() {
            this.accounts()
        },

        methods: {
            ...mapActions('accounts', ['accounts']),

            getLogin(login) {
                this.$refs.popup.login = login
            }
        }
    }
</script>
