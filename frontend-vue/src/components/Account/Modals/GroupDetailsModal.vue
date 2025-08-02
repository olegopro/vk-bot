<script setup lang="ts">
  import { computed, getCurrentInstance } from 'vue'
  import { useModal } from '@/composables/useModal'
  import { useAccountStore } from '@/stores/AccountStore'

  const modalId = getCurrentInstance()?.type.__name
  const { closeModal } = useModal()
  const accountStore = useAccountStore()

  // Используем данные из API через .data
  const currentGroupData = computed(() => accountStore.fetchGroupData.data)
</script>

<template>
  <div class="modal fade" :id="modalId" tabindex="-1" aria-labelledby="Group details" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <i class="bi bi-people-fill me-2 fs-3" />
          <h1 class="modal-title fs-5">{{ currentGroupData?.name }}</h1>
          <button type="button" class="btn-close" @click="closeModal(modalId)" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="row">
            <div class="col-12">
              <div class="d-flex account flex-wrap">

                <div class="d-flex">
                  <div class="position-relative">
                    <div v-if="!currentGroupData?.photo_200" class="stub">
                      <div class="spinner-border" style="width: 3rem; height: 3rem;" role="status">
                      </div>
                    </div>

                    <img v-else width="200" height="200" :src="currentGroupData?.photo_200" alt="">

                  </div>

                  <p class="m-3 mt-0"><b>Статус: </b>{{ currentGroupData?.status }}</p>
                </div>

                <div class="col-12 d-flex flex-column justify-content-between">
                  <div>
                    <h5>{{ currentGroupData?.screen_name }}</h5>
                  </div>
                </div>
                <div class="col-12 d-flex flex-column">
                  <h4 class="mb-3">
                    <i class="bi bi-person-heart me-3 fs-3 text-danger" />
                    Подписчики - {{ currentGroupData?.members_count }}
                  </h4>
                  <h4>
                    <i class="bi bi-buildings-fill me-3 fs-3 text-primary" />
                    Город - {{ currentGroupData?.city?.title ?? 'Не указан' }}
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

  :deep(.stub) {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 200px;
    height: 200px;
    border-right: 1px solid whitesmoke;
  }
</style>
