<script setup lang="ts">
  import { useAccountsStore } from '@/stores/AccountsStore'
  import { onMounted } from 'vue'
  import { useModal } from '@/composables/useModal'
  import AddAccountModal from '@/components/Accounts/Modals/AddAccountModal.vue'
  import DeleteAccountModal from '@/components/Accounts/Modals/DeleteAccountModal.vue'
  import { showSuccessNotification } from '@/helpers/notyfHelper'

  const { showModal } = useModal()
  const accountsStore = useAccountsStore()

  const showAddAccount = () => showModal(AddAccountModal)
  const showDeleteAccount = (login: string, id: number) => showModal(DeleteAccountModal, { login, accountId: id })

  onMounted(() => accountsStore.fetchAccounts.execute()
    .then((response) => showSuccessNotification(response.message)))
</script>

<template>
  <div class="row mb-3 align-items-center">
    <div class="col d-flex align-items-center">
      <h1 class="h2 mb-0 me-3">Список аккаунтов</h1>

      <h6 class="mb-0" style="pointer-events: none">
        <span class="badge btn btn-secondary d-flex items-center fw-bold" style="padding: 8px">
          <template v-if="accountsStore.fetchAccounts.loading">
            <span class="spinner-border" role="status" style="width: 12px; height: 12px;">
              <span class="visually-hidden">Загрузка...</span>
            </span>
          </template>

          <template v-else>
            <span class="me-1">{{ accountsStore.fetchAccounts.data?.length || 0 }}</span>
          </template>
        </span>
      </h6>
    </div>

    <div class="col">
      <button class="btn btn-success btn-action float-end" type="button" @click="showAddAccount">
        Добавить аккаунт
      </button>
    </div>

  </div>

  <div class="row">
    <div class="col-12">
      <PerfectScrollbar class="ps-table">
        <table class="table table-hover">
          <thead>
            <tr>
              <th scope="col" style="width: 180px;">#</th>
              <th scope="col" style="width: 400px;">Имя и фамилия</th>
              <th scope="col" style="width: 250px;">Логин</th>
              <th scope="col" style="width: 250px;">Действия</th>
              <th scope="col">День рождения</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="account in accountsStore.fetchAccounts.data" :key="account.account_id">
              <th scope="row">ID {{ account.account_id }}</th>
              <td>{{ account.first_name }} {{ account.last_name }}</td>
              <td>{{ account.screen_name }}</td>

              <td>
                <router-link custom :to="{ name: 'Account', params: { id: account.account_id } }" v-slot="{ navigate }">
                  <a class="btn btn-primary me-2 button-style" @click="navigate">
                    <i class="bi bi-info-circle" />
                  </a>
                </router-link>

                <button class="btn btn-danger button-style" type="button"
                  @click="showDeleteAccount(account.screen_name, account.account_id)">
                  <i class="bi bi-trash3" />
                </button>
              </td>

              <td>{{ account.birthday_date }}</td>
            </tr>

            <tr v-if="accountsStore.fetchAccounts.loading" style="height: 55px;">
              <td colspan="7">
                <div class="spinner-border" role="status" style="position: relative; top: 3px;">
                  <span class="visually-hidden">Загрузка...</span>
                </div>
              </td>
            </tr>

            <tr v-if="accountsStore.fetchAccounts.data?.length === 0 && !accountsStore.fetchAccounts.loading" class="pe-none">
              <td colspan="7" style="height: 55px;">
                Список аккаунтов пуст
              </td>
            </tr>
          </tbody>
        </table>
      </PerfectScrollbar>
    </div>
  </div>

</template>
