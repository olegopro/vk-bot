<script setup lang="ts">
  import { useAccountStore } from '@/stores/AccountStore'

  interface NewsfeedSearchProps {
    accountId: string
    taskCount: number
  }

  const props = defineProps<NewsfeedSearchProps>()
  const emit = defineEmits<{
    (e: 'success', response: any): void
    (e: 'cancel'): void
  }>()

  const accountStore = useAccountStore()

  const addFeedTask = (): void => {
    accountStore.addPostsToLike.execute({
      postsData: {
        account_id: props.accountId,
        task_count: props.taskCount
      }
    })
      .then(response => emit('success', response))
  }
</script>

<template>
  <form @submit.prevent="addFeedTask">
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" @click="emit('cancel')">Отмена</button>
      <button type="submit" class="btn btn-success" :disabled="accountStore.addPostsToLike.loading">
        Создать
        <span v-if="accountStore.addPostsToLike.loading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
      </button>
    </div>
  </form>
</template>
