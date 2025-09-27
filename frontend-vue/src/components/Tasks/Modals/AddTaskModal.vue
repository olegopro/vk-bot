<script setup lang="ts">
  import { ref, getCurrentInstance } from 'vue'
  import { useAccountsStore } from '@/stores/AccountsStore'
  import { useTasksStore } from '@/stores/TasksStore'
  import { showSuccessNotification } from '@/helpers/notyfHelper'
  import { useModal } from '@/composables/useModal'
  import NewsfeedSearch from './AddTaskModal/NewsfeedSearch.vue'
  import CitySearch from './AddTaskModal/CitySearch.vue'
  import type { ApiResponseWrapper } from '@/models/ApiModel'
  import type { VkNewsFeedItem } from '@/types/vkontakte'
  import type { CreateTasksResponse } from '@/models/AccountsModel'

  const accountsStore = useAccountsStore()
  const tasksStore = useTasksStore()
  const { closeModal } = useModal()

  const modalId = getCurrentInstance()?.type.__name

  const accountId = ref<string>('selectAccount')
  const taskCount = ref<number>(10)
  const searchType = ref<'newsfeed' | 'city'>('newsfeed')

  // TODO: Нужно проверить точно
  const handleTaskSuccess = (response: ApiResponseWrapper<VkNewsFeedItem[]> | ApiResponseWrapper<CreateTasksResponse>): void => {
    modalHide()
    showSuccessNotification(response.message)
    tasksStore.fetchTasks.execute()
  }

  const modalHide = (): void => {
    closeModal(modalId)
    searchType.value = 'newsfeed'
  }
</script>

<template>
  <div class="modal fade" :id="modalId" tabindex="-1" aria-labelledby="Add task" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="Delete task">Добавление задачи</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

          <!-- Выбор аккаунта и количества постов -->
          <select class="form-select mb-3" aria-label="Default select example" v-model="accountId">
            <option disabled selected value="selectAccount">Выберите аккаунт</option>
            <option v-for="account in accountsStore.fetchAccounts.data" :key="account.account_id" :value="account.account_id">
              {{ account.screen_name }} ({{ account.first_name }} {{ account.last_name }})
            </option>
          </select>

          <div class="input-group mb-3">
            <span class="input-group-text">Количество постов для лайков</span>
            <input type="text" class="form-control" placeholder="По умолчанию 10 постов" v-model="taskCount">
          </div>

          <!-- Переключатели режимов поиска -->
          <div class="form-check form-check-inline mb-3">
            <input class="form-check-input" type="radio" name="searchType" id="newsfeedSearch" value="newsfeed" v-model="searchType">
            <label class="form-check-label" for="newsfeedSearch">Поиск по ленте</label>
          </div>
          <div class="form-check form-check-inline mb-3">
            <input class="form-check-input" type="radio" name="searchType" id="citySearch" value="city" v-model="searchType">
            <label class="form-check-label" for="citySearch">Поиск по городу</label>
          </div>

          <!-- Компоненты для разных типов поиска -->
          <NewsfeedSearch
            v-if="searchType === 'newsfeed'"
            :account-id="accountId"
            :task-count="taskCount"
            @success="handleTaskSuccess"
            @cancel="modalHide"
          />

          <CitySearch
            v-if="searchType === 'city'"
            :account-id="accountId"
            :task-count="taskCount"
            @success="handleTaskSuccess"
            @cancel="modalHide"
          />

        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
  :deep(.modal-footer) {
    padding: 0 !important;
  }
</style>
