<template>
    <div class="modal fade" id="addTask" tabindex="-1" aria-labelledby="Add task" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form @submit.prevent="addNewTask" class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="Delete task">Добавление задачи</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <select class="form-select mb-3" aria-label="Default select example" v-model="accountId">
                        <option disabled selected value="selectAccount">Выберите аккаунт</option>
                        <option v-for="account in getAccounts" :key="account.id" :value="account.account_id">
                            {{ account.screen_name }} ({{ account.first_name }} {{ account.last_name }})
                        </option>
                    </select>

                    <div class="input-group mb-3">
                        <span class="input-group-text">Количество постов для лайков</span>
                        <input type="text" class="form-control" placeholder="По умолчанию 10 постов" v-model="taskCount">
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="modalHide">Отмена</button>
                    <button type="submit" class="btn btn-success" :disabled="disablePost">
                        Создать
                        <span v-if="loading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex'
    import { Modal } from 'bootstrap'

    export default {
        data() {
            return {
                accountId: 'selectAccount',
                disablePost: true,
                loading: false,
                taskCount: 10
            }
        },

        computed: {
            ...mapGetters('accounts', ['getAccounts'])
        },

        watch: {
            accountId() {
                this.accountId === 'selectAccount'
                    ? this.disablePost = true
                    : this.disablePost = false
            }
        },

        mounted() {
            this.accounts()
            this.modal = new Modal(document.getElementById('addTask'))
        },

        methods: {
            ...mapActions('accounts', ['accounts']),
            ...mapActions('account', ['addPostsToLike']),

            modalHide() {
                this.modal.hide()
            },

            addNewTask() {
                this.disablePost = true
                this.loading = true
                this.addPostsToLike({ accountId: this.accountId, taskCount: this.taskCount })
                    .then(() => {
                        this.modalHide()
                        this.disablePost = false
                        this.loading = false
                        this.accountId = 'selectAccount'
                    })
            }

        }
    }
</script>

<style scoped lang="scss">

</style>
