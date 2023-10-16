<template>
    <div class="modal fade" id="deleteAccount" tabindex="-1" aria-labelledby="Delete account" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form @submit.prevent="deleteAccount" class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="Delete account">Удаление аккаунта</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Удалить аккаунт с логином <strong>{{ login }}</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" data-bs-dismiss="modal" class="btn btn-danger">Удалить</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
    import { defineProps } from 'vue'
    import { useAccountsStore } from '../../../stores/AccountsStore'

    const accountsStore = useAccountsStore()

    const props = defineProps(['login', 'id'])

    const deleteAccount = () => accountsStore.deleteAccount(props.id)
</script>
