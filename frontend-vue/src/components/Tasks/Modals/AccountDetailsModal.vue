<script setup lang="ts">
  import { computed, getCurrentInstance } from 'vue'
  import { useModal } from '@/composables/useModal'
  import { useAccountStore } from '@/stores/AccountStore'
  import OnlineStatus from '@/components/Account/OnlineStatus.vue'

  const modalId = getCurrentInstance()?.type.__name
  const { closeModal } = useModal()
  const accountStore = useAccountStore()

  const formattedSex = computed(() => {
    switch (accountStore.fetchOwnerData.data?.sex) {
      case 1:
        return 'Женский'
      case 2:
        return 'Мужской'
      default:
        return 'Не указан'
    }
  })

  const date = (timestamp: number) => new Date(timestamp * 1000).toLocaleTimeString('ru-RU')
</script>

<template>
  <div class="modal fade" :id="modalId" tabindex="-1" aria-labelledby="Task details" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" v-if="accountStore.fetchOwnerData.data">
          <h1 class="modal-title fs-4 me-3">
            <span>{{ accountStore.fetchOwnerData.data.first_name }} {{ accountStore.fetchOwnerData.data.last_name }}</span>

          </h1>
          <OnlineStatus class="h-6" :type="accountStore.fetchOwnerData.data.online === 0 ? 'offline' : 'online'" />
          <button type="button" class="btn-close" @click="closeModal(modalId)" aria-label="Close"></button>
        </div>

        <div class="modal-body py-2" v-if="accountStore.fetchOwnerData.data">
          <div class="row">
            <div class=" col-6">
              <img :src="accountStore.fetchOwnerData.data.photo_200" class="rounded-1 w-100" alt="">
            </div>

            <div class="col-6">
              <p class="mb-1"><b>Страна:</b> {{ accountStore.fetchOwnerData.data?.country?.title }}</p>
              <p class="mb-1"><b>Город:</b> {{ accountStore.fetchOwnerData.data?.city?.title }}</p>
              <p class="mb-1"><b>Друзья:</b> {{ accountStore.fetchOwnerData.data.friends_count }}</p>
              <p class="mb-1"><b>Подписчики:</b> {{ accountStore.fetchOwnerData.data.followers_count }}</p>
              <p class="mb-1"><b>Пол:</b> {{ formattedSex }}</p>
              <p class="mb-1"><b>День рождения:</b> {{ accountStore.fetchOwnerData.data.birthday_date }}</p>
              <p class="mb-0">
                <b>{{ formattedSex === 'Мужской' ? 'Был' : 'Была' }} в сети: </b>
                {{
                  accountStore.fetchOwnerData.data.last_seen?.time
                    ? date(accountStore.fetchOwnerData.data.last_seen.time)
                    : 'Неизвестно'
                }}
              </p>
            </div>
          </div>

          <p class="mt-3 mb-0" v-if="accountStore.fetchOwnerData.data.status">
            <b>Статус:</b> {{ accountStore.fetchOwnerData.data.status }}
          </p>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeModal(modalId)">Закрыть</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
  .modal-body {
    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center;
    }
  }
</style>
