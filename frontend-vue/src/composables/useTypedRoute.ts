import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { TaskStatus } from '@/models/TaskModel'
import type { TasksRouteParams } from '@/router/routerPaths'

export function useTasksRoute() {
  const route = useRoute()
  
  const params = computed<TasksRouteParams>(() => {
    const rawParams = route.params
    
    return {
      status: isValidTaskStatus(rawParams.status) ? rawParams.status : '',
      accountId: typeof rawParams.accountId === 'string' ? rawParams.accountId : ''
    }
  })
  
  return params
}

function isValidTaskStatus(value: unknown): value is TaskStatus {
  return typeof value === 'string' && 
    (['queued', 'done', 'failed', ''].includes(value))
}
