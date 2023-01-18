<template>

    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">

        <symbol id="info" width="20" height="20" fill="currentColor" class="bi bi-info-circle" viewBox="0 0 20 20">
            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
            <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z" />
        </symbol>

        <symbol id="delete" width="20" height="20" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 20 20">
            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
            <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z" />
        </symbol>
    </svg>

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
                    <button type="button" class="btn btn-primary me-2">
                        <svg width="16" height="20">
                            <use xlink:href="#info"></use>
                        </svg>
                    </button>

                    <button type="button" class="btn btn-danger">
                        <svg width="16" height="20">
                            <use xlink:href="#delete"></use>
                        </svg>
                    </button>

                </td>
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
