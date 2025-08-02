<script setup lang="ts">
  import { computed, getCurrentInstance } from 'vue'
  import { useModal } from '@/composables/useModal'
  import { useAccountStore } from '@/stores/AccountStore'
  import OnlineStatus from '../OnlineStatus.vue'

  const modalId = getCurrentInstance()?.type.__name
  const { closeModal } = useModal()
  const accountStore = useAccountStore()

  // Используем данные из API через .data
  const currentAccountData = computed(() => accountStore.fetchOwnerData.data)

  // Метод для форматирования даты
  const date = (timestamp: number) => new Date(timestamp * 1000).toLocaleTimeString('ru-RU')
</script>

<template>
  <div class="modal fade" :id="modalId" tabindex="-1" aria-labelledby="Account details" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <i class="bi bi-person-fill me-2 fs-3" />
          <h1 class="modal-title fs-5">
            {{ currentAccountData?.first_name }} {{ currentAccountData?.last_name }} {{ currentAccountData?.name }}
          </h1>
          <button type="button" class="btn-close" @click="closeModal(modalId)" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="row">
            <div class="col-12">
              <div class="d-flex account flex-wrap">
                <div class="d-flex">
                  <div class="position-relative">
                    <div v-if="!currentAccountData?.photo_200" class="stub">
                      <div class="spinner-border" style="width: 3rem; height: 3rem;" role="status">
                      </div>
                    </div>

                    <img v-else width="200" height="200" :src="currentAccountData?.photo_200" alt="">

                    <OnlineStatus 
                      v-if="currentAccountData?.photo_200" 
                      :type="currentAccountData?.online === 0 ? 'offline' : 'online'" 
                      class="online-status" 
                    />
                  </div>

                  <p class="m-3 mt-0"><b>Статус: </b>{{ currentAccountData?.status }}</p>
                </div>

                <div class="col-12 d-flex flex-column justify-content-between">
                  <div>
                    <h5>{{ currentAccountData?.screen_name }}</h5>
                  </div>

                  <div class="mb-3">
                    <p>Последняя активность - {{ date(currentAccountData?.last_seen?.time) }}</p>
                  </div>
                </div>
                <div class="col-12 d-flex flex-column">
                  <h4 class="mb-3">
                    <i class="bi bi-person-fill-check me-3 fs-3 text-success" />
                    Друзья - {{ currentAccountData?.friends_count }}
                  </h4>
                  <h4 class="mb-3">
                    <i class="bi bi-person-heart me-3 fs-3 text-danger" />
                    Подписчики - {{ currentAccountData?.followers_count }}
                  </h4>
                  <h4>
                    <i class="bi bi-buildings-fill me-3 fs-3 text-primary" />
                    Город - {{ currentAccountData?.city?.title }}
                  </h4>
                </div>

              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeModal(modalId)">Закрыть</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
  :deep(img) {
    width: 200px;
    height: 200px;
    border-radius: 0.5rem;
  }

  :deep(.online-status) {
    position: absolute;
    right: 0;
  }

  :deep(.stub) {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 200px;
    height: 200px;
    border-right: 1px solid whitesmoke;
  }
</style>
