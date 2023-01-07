<template>
    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Имя и фамилия</th>
                <th scope="col">Логин</th>
                <th scope="col">Лайки</th>
                <th scope="col">Добавлен</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="account in getAccounts" :key="account.id">
                <th scope="row">{{ account.id }}</th>
                <td>{{ account.username }}</td>
                <td>Логин</td>
                <td>{{ account.likes_count }}</td>
                <td>{{ account.created_at }}</td>
            </tr>
        </tbody>
    </table>

</template>

<script>
    import { mapActions, mapGetters } from 'vuex'

    export default {
        mounted() {
            this.accounts()
        },

        computed: {
            ...mapGetters('accounts', ['getAccounts'])
        },

        methods: {
            ...mapActions('accounts', ['accounts'])
        }
    }
</script>
