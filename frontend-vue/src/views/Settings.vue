<script setup lang="ts">
  import { onMounted } from 'vue'
  import { useSettingsStore } from '@/stores/SettingsStore'

  const settingsStore = useSettingsStore()

  const save = () => {
    if (settingsStore.fetchSettings.data) {
      settingsStore.saveSettings.execute(settingsStore.fetchSettings.data)
    }
  }

  onMounted(() => settingsStore.fetchSettings.execute())
</script>

<template>
  <div class="row mb-3 align-items-center">
    <div class="col">
      <h1 class="h2">Настройки</h1>
    </div>
    <div class="col">
      <button
        type="submit"
        form="save-settings"
        class="btn btn-success btn-action float-end"
        :disabled="settingsStore.saveSettings.loading"
      >
        Сохранить
        <span v-show="settingsStore.saveSettings.loading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" />
      </button>
    </div>
  </div>

  <div class="row">
    <div class="col" v-if="!settingsStore.fetchSettings.loading && settingsStore.fetchSettings.data">
      <form @submit.prevent="save" class="settings" id="save-settings">
        <div class="row align-items-center justify-content-between">
          <div class="col-6">
            <div class="form-check form-switch mb-1 d-flex align-items-center">
              <input id="showFriends"
                class="form-check-input"
                role="switch"
                type="checkbox"
                v-model="settingsStore.fetchSettings.data.show_friends"
              >
              <label class="form-check-label" for="showFriends">Показывать друзей</label>
            </div>
            <div class="form-check form-switch d-flex align-items-center">
              <input id="showFollowers"
                class="form-check-input"
                role="switch"
                type="checkbox"
                v-model="settingsStore.fetchSettings.data.show_followers"
              >
              <label class="form-check-label" for="showFollowers">Показывать подписчиков</label>
            </div>
          </div>
          <div class="col-6 d-flex justify-content-end">
            <div class="input-group">
              <span class="input-group-text">Задержка между задачами</span>
              <input type="number" class="form-control" v-model="settingsStore.fetchSettings.data.task_timeout">
              <span class="input-group-text">сек.</span>
            </div>
          </div>
        </div>
      </form>
    </div>
    <div class="col" v-else>
      <div class="d-flex justify-content-center">
        <div class="spinner-border" role="status">
          <span class="visually-hidden">Загрузка...</span>
        </div>
      </div>
    </div>
  </div>

</template>

<style scoped lang="scss">
  .settings {
    box-shadow: 0 1px 27px 0 rgba(34, 60, 80, 0.2);
    border-radius: 0.675rem;
    padding: 2rem;

    input {
      &:focus {
        box-shadow: none;
      }

      &.form-check-input {
        margin-top: 0;
        height: 24px;
        width: 48px;
      }

      &.form-control {
        max-width: 48px;
      }
    }

    label {
      height: auto;
      margin-bottom: 2px;
      padding-left: 0.8rem;
      font-size: 19px;
    }

    .input-group {
      width: fit-content;
    }
  }
</style>
