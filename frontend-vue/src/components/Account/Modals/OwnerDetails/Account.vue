<script setup>
  import { defineProps } from 'vue'
  import OnlineStatus from '../../OnlineStatus.vue'

  // Определение props
  const { accountData } = defineProps(['accountData'])

  // Метод для форматирования даты
  const date = timestamp => new Date(timestamp * 1000).toLocaleTimeString('ru-RU')
</script>

<template>
  <div class="modal-header">
    <i class="bi bi-person-fill me-2 fs-3" />
    <h1 class="modal-title fs-5">{{ accountData?.first_name }} {{ accountData?.last_name }} {{ accountData?.name }}</h1>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
  </div>
  <div class="modal-body">
    <div class="row">
      <div class="col-12">
        <div class="d-flex account flex-wrap">

          <div class="d-flex">
            <div class="position-relative">
              <div v-if="!accountData?.photo_200" class="stub">
                <div class="spinner-border" style="width: 3rem; height: 3rem;" role="status">
                </div>
              </div>

              <img v-else width="200" height="200" :src="accountData?.photo_200" alt="">

              <OnlineStatus v-if="accountData?.photo_200" :type="accountData?.online === 0 ? 'offline' : 'online'" class="online-status" />
            </div>

            <p class="m-3 mt-0"><b>Статус: </b>{{ accountData?.status }}</p>
          </div>

          <div class="col-12 d-flex flex-column justify-content-between">
            <div>
              <h5>{{ accountData?.screen_name }}</h5>
            </div>

            <div class="mb-3">
              <p>Последняя активность - {{ date(accountData?.last_seen?.time) }}</p>
            </div>
          </div>
          <div class="col-12 d-flex flex-column">
            <h4 class="mb-3">
              <i class="bi bi-person-fill-check me-3 fs-3 text-success" />
              Друзья - {{ accountData?.friends_count }}
            </h4>
            <h4 class="mb-3">
              <i class="bi bi-person-heart me-3 fs-3 text-danger" />
              Подписчики - {{ accountData?.followers_count }}
            </h4>
            <h4>
              <i class="bi bi-buildings-fill me-3 fs-3 text-primary" />
              Город - {{ accountData?.city?.title }}
            </h4>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>
