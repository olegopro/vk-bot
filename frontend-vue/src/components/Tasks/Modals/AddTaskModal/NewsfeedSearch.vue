<script setup lang="ts">
  import { useAccountStore } from '@/stores/AccountStore'
  import type { ApiResponseWrapper } from '@/models/ApiModel'
  import type { VkNewsFeedItem } from '@/types/vkontakte'

  interface NewsfeedSearchProps {
    accountId: string
    taskCount: number
  }

  const props = defineProps<NewsfeedSearchProps>()
  const emit = defineEmits<{
    success: [response: ApiResponseWrapper<VkNewsFeedItem[]>]
    cancel: []
  }>()

  const accountStore = useAccountStore()

  const addFeedTask = (): void => {
    accountStore.addPostsToLike.execute({
      postsData: {
        account_id: props.accountId,
        task_count: props.taskCount
      }
    })
      .then((response: ApiResponseWrapper<VkNewsFeedItem[]>) => emit('success', response))
  }

  defineExpose({ addFeedTask })
</script>
