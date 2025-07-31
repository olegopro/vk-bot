<script setup lang="ts">
  import { ref, onMounted, computed } from 'vue'
  import { useSettingsStore } from '@/stores/SettingsStore'
  import type { Settings } from '@/models/SettingsModel'
  import { Nullable } from '@types'

  const settingsStore = useSettingsStore()

  const showFriends = ref<boolean>(false)
  const showFollowers = ref<boolean>(false)
  const taskTimeout = ref<number>(0)

  const currentSettings = computed<Nullable<Settings>>(() => {
    const data = (settingsStore.fetchSettings.data as any)?.value
    if (data && data.length > 0) {
      return data[0]
    }
    return null
  })

  onMounted(async () => {
    await settingsStore.fetchSettings.execute()

    if (currentSettings.value) {
      showFriends.value = currentSettings.value.show_friends
      showFollowers.value = currentSettings.value.show_followers
      taskTimeout.value = currentSettings.value.task_timeout
    }
  })

  const save = async (): Promise<void> => {
    await settingsStore.saveSettings.execute({
      show_followers: showFollowers.value,
      show_friends: showFriends.value,
      task_timeout: taskTimeout.value
    })

    // Обновляем данные после сохранения
    await settingsStore.fetchSettings.execute()
  }
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
        <span v-show="settingsStore.saveSettings.loading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
      </button>
    </div>
  </div>

  <div class="row">
    <div class="col" v-if="!settingsStore.fetchSettings.loading">
      <form @submit.prevent="save" class="settings" id="save-settings">
        <div class="row align-items-center justify-content-between">
          <div class="col-6">
            <div class="form-check form-switch mb-1 d-flex align-items-center">
              <input id="showFriends"
                :checked="showFriends"
                class="form-check-input"
                role="switch"
                type="checkbox"
                v-model="showFriends"
              >
              <label class="form-check-label" for="showFriends">Показывать друзей</label>
            </div>
            <div class="form-check form-switch d-flex align-items-center">
              <input id="showFollowers"
                :checked="showFollowers"
                class="form-check-input"
                role="switch"
                type="checkbox"
                v-model="showFollowers"
              >
              <label class="form-check-label" for="showFollowers">Показывать подписчиков</label>
            </div>
          </div>
          <div class="col-6 d-flex justify-content-end">
            <div class="input-group">
              <span class="input-group-text">Задержка между задачами</span>
              <input type="number" class="form-control" v-model="taskTimeout">
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
