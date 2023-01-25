<template>
    <div class="modal fade" id="deleteAccount" tabindex="-1" aria-labelledby="Delete account" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="Delete account">Удаление аккаунта</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Удалить аккаунт с логином <strong>{{ login }}</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button @click="deleteAccount(id)" type="button" data-bs-dismiss="modal" class="btn btn-danger">Удалить</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapActions } from 'vuex'

    export default {
        data() {
            return {
                login: '',
                id: ''
            }
        },

        methods: {
            ...mapActions('account', ['deleteAccount'])
        }
    }
</script>

<style scoped lang="scss">

</style>
