<template>
    <div class="modal fade" id="addAccount" tabindex="-1" aria-labelledby="Add account" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form @submit.prevent="account" class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="Add account">Добавление аккаунта</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="input-group">
                        <span class="input-group-text">Access token</span>

                        <input
                            aria-describedby="access-token"
                            aria-label="Access token"
                            class="form-control"
                            type="text"
                            v-model="accessToken"
                        >

                    </div>
                    <div v-if="errorMessage" class="alert alert-danger mt-3 mb-0" role="alert">
                        {{ errorMessage }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="modalHide">Отмена</button>
                    <button type="submit" class="btn btn-success" :disabled="disableSubmit">Добавить</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import { mapActions } from 'vuex'
    import { Modal } from 'bootstrap'

    export default {
        data() {
            return {
                accessToken: null,
                modal: null,
                showError: false,
                errorMessage: null,
                disableSubmit: true
            }
        },

        watch: {
            accessToken(data) {
                data.length > 3
                    ? this.disableSubmit = false
                    : this.disableSubmit = true
            }
        },

        mounted() {
            this.modal = new Modal(document.getElementById('addAccount'))
        },

        methods: {
            ...mapActions('account', ['addAccount']),

            account() {
                this.addAccount(this.accessToken)
                    .then(() => {
                        this.modal.hide()
                    })
                    .catch(error => {
                        this.errorMessage = error.response.data.message
                        this.disableSubmit = true
                    })
            },

            modalHide() {
                this.modal.hide()
            }
        }
    }
</script>

<style scoped lang="scss">

</style>
