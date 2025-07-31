<script setup>
  import { onMounted, computed, reactive } from 'vue'
  import { useRoute } from 'vue-router'
  import { useAccountStore } from '@/stores/AccountStore'

  const route = useRoute()
  const accountStore = useAccountStore()

  const state = reactive({
    id: route.params.id,
    friends: computed(() => accountStore.getAccountFriends(state.id))
  })

  onMounted(() => accountStore.fetchAccountFriends(state.id))
</script>

<template>
  <div class="row mt-5 mb-5">
    <h1>Друзья</h1>
    <div class="col-2" v-for="friend in state.friends" :key="friend.id">
      <div class="card">
        <img class="bd-placeholder-img card-img-top"
          width="100%"
          height="200"
          alt=""
          :src="friend.photo_200"
        />
        <div class="card-body">
          <p class="card-text">{{ friend.first_name }} {{ friend.last_name }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
  .card {
    border: none;
    box-shadow: 0 1px 27px 0 rgba(34, 60, 80, 0.2);

    .bd-placeholder-img {
      object-fit: cover;
    }

    .card-body {
      .card-text {
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
      }
    }
  }
</style>
