<template>
    <div class="modal fade" id="addAccount" tabindex="-1" aria-labelledby="Add account" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form @submit.prevent="addAccount(accessToken)" class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="Add account">Добавление аккаунта</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="input-group mb-3">
                        <span class="input-group-text">Access token</span>
                        <input aria-describedby="access-token"
                               aria-label="Access token"
                               class="form-control"
                               type="text"
                               v-model="accessToken"
                        >
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-success" data-bs-dismiss="modal">Добавить</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import { mapActions } from 'vuex'

    export default {
        data() {
            return {
                accessToken: null
            }
        },

        methods: {
            ...mapActions('account', ['addAccount'])
        }
    }
</script>

<style scoped lang="scss">

</style>
