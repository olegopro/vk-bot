<template>
    <div class="modal fade" id="addTask" tabindex="-1" aria-labelledby="Add task" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form @submit.prevent="addPostsToLike(9121607)" class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="Delete task">Добавление задачи</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Аккаунты</p>
                    <select class="form-select" aria-label="Default select example">
                        <option disabled selected>Выберите аккаунт</option>
                        <option v-for="account in getAccounts" :key="account.id" :value="account.screen_name">
                            {{ account.screen_name }} ({{ account.first_name }} {{ account.last_name }})
                        </option>

                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-success">Создать</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex'

    export default {
        computed: {
            ...mapGetters('accounts', ['getAccounts'])
        },

        mounted() {
            this.accounts()
        },

        methods: {
            ...mapActions('accounts', ['accounts']),
            ...mapActions('account', ['addPostsToLike'])
        }

    }
</script>

<style scoped lang="scss">

</style>
