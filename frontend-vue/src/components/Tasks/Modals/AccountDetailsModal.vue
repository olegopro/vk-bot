<script setup lang="ts">
  import { computed, defineProps, getCurrentInstance, onMounted, onUnmounted } from 'vue'
  import { useModal } from '@/composables/useModal'
  import OnlineStatus from '@/components/Account/OnlineStatus.vue'

  const props = defineProps<{
    accountData: object
  }>()

  const modalId = getCurrentInstance()?.type.__name
  const { closeModal } = useModal()

  const formattedSex = computed(() => {
    switch (props.accountData.sex) {
      case 1:
        return 'Женский'
      case 2:
        return 'Мужской'
      default:
        return 'Не указан'
    }
  })

  const date = (timestamp) => new Date(timestamp * 1000).toLocaleTimeString('ru-RU')

  onMounted(() => console.log('AccountDetailsModal onMounted'))
  onUnmounted(() => console.log('AccountDetailsModal onUnmounted'))
</script>

<template>
  <div class="modal fade" :id="modalId" tabindex="-1" aria-labelledby="Task details" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" v-if="props.accountData">
          <h1 class="modal-title fs-4 me-3">
            <span>{{ props.accountData.first_name }} {{ props.accountData.last_name }}</span>

          </h1>
          <OnlineStatus class="h-6" :type="props.accountData.online === 0 ? 'offline' : 'online'" />
          <button type="button" class="btn-close" @click="closeModal(modalId)" aria-label="Close"></button>
        </div>

        <div class="modal-body py-2" v-if="props.accountData">
          <div class="row">
            <div class=" col-6">
              <img :src="props.accountData.photo_200" class="rounded-1 w-100" alt="">
            </div>

            <div class="col-6">
              <p class="mb-1"><b>Страна:</b> {{ props.accountData?.country?.title }}</p>
              <p class="mb-1"><b>Город:</b> {{ props.accountData?.city?.title }}</p>
              <p class="mb-1"><b>Друзья:</b> {{ props.accountData.friends_count }}</p>
              <p class="mb-1"><b>Подписчики:</b> {{ props.accountData.followers_count }}</p>
              <p class="mb-1"><b>Пол:</b> {{ formattedSex }}</p>
              <p class="mb-1"><b>День рождения:</b> {{ props.accountData.bdate }}</p>
              <p class="mb-0">
                <b>{{ formattedSex === 'Мужской' ? 'Был' : 'Была' }} в сети: </b> {{ date(accountData.last_seen?.time) }}
              </p>
            </div>
          </div>

          <p class="mt-3 mb-0" v-if="props.accountData.status">
            <b>Статус:</b> {{ props.accountData.status }}
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
  #ownerDetailsModal {
    .modal-body {
      img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
      }
    }
  }
</style>
