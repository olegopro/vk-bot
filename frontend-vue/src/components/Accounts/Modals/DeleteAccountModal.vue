<script setup lang="ts">
  import { defineProps } from 'vue'
  import { useAccountsStore } from '@/stores/AccountsStore'
  import { useModal } from '@/composables/useModal'

  const modalId = 'deleteAccountModal'
  const { closeModal } = useModal()
  const accountsStore = useAccountsStore()

  const { login, accountId } = defineProps<{
    login: string,
    accountId: number
  }>()

  const deleteAccount = () => accountsStore.deleteAccount.execute({ accountId }).then(() => closeModal(modalId))
</script>

<template>
  <div class="modal fade" :id="modalId" tabindex="-1" aria-labelledby="Delete account" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form @submit.prevent="deleteAccount" class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="Delete account">Удаление аккаунта</h1>
          <button type="button" class="btn-close" @click="closeModal(modalId)" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Удалить аккаунт с логином <strong>{{ login }}</strong></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeModal(modalId)">Отмена</button>
          <button type="submit" class="btn btn-danger">Удалить</button>
        </div>
      </form>
    </div>
  </div>
</template>
