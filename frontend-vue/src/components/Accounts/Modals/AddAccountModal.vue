<script setup>
  import { computed, ref } from 'vue'
  import { useAccountsStore } from '../../../stores/AccountsStore'
  import { useModal } from '@/composables/useModal'

  const modalId = 'addAccountModal'
  const accountsStore = useAccountsStore()
  const accessToken = ref(null)
  const { closeModal } = useModal()

  const addAccount = () => {
    accountsStore.addAccount.execute({ accessToken: accessToken.value })
      .then(() => closeModal(modalId))
      .catch(() => disableSubmit.value = true)
  }

  const disableSubmit = computed(() => !(accessToken.value && accessToken.value.length > 3))
</script>

<template>
  <div class="modal fade" :id="modalId" tabindex="-1" aria-labelledby="Add account" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form @submit.prevent="addAccount" class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="Add account">Добавление аккаунта</h1>
          <button type="button" class="btn-close" @click="closeModal(modalId)" aria-label="Close"></button>
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

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeModal(modalId)">Отмена</button>
          <button type="submit" class="btn btn-success" :disabled="disableSubmit">Добавить</button>
        </div>
      </form>
    </div>
  </div>
</template>
