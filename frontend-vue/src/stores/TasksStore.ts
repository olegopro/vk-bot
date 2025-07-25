import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from '@/helpers/axiosConfig'
import { showErrorNotification, showSuccessNotification } from '@/helpers/notyfHelper'
import useApi from '@/composables/useApi'
import {
  Task,
  TaskDetails,
  TaskStatuses,
  TasksListResponse,
  TaskDetailsResponse,
  TaskCountByStatusResponse,
  DeleteTaskResponse,
  DeleteLikeResponse,
  CreateTasksRequest,
  TaskStatus
} from '@/models/TaskModel'

export const useTasksStore = defineStore('tasks', () => {
  const tasks = ref<Task[]>([])
  const totalTasksCount = ref<number | null>(null)
  const totalTasksQueued = ref<number | null>(null)
  const totalTasksFailed = ref<number | null>(null)
  const totalTasksDone = ref<number | null>(null)
  const taskCountByStatus = ref<TaskStatuses | null>(null)
  const isLoading = ref<boolean>(false)
  const isTaskDetailsLoading = ref<number | null>(null)
  const taskDetails = ref<TaskDetails | null>(null)

  /**
   * Получает список задач
   */
  const fetchTasks = useApi(async (parameters?: {
    status?: TaskStatus
    accountId?: string | number
  }) => {
    const { status = '', accountId = '' } = parameters || {}

    const url = `tasks${status ? `/${status}` : ''}${accountId ? `/${accountId}` : ''}`

    return axios.get<TasksListResponse>(url)
      .then(response => {
        tasks.value = response.data.data.tasks.data
        totalTasksCount.value = response.data.data.total
        totalTasksFailed.value = response.data.data.statuses.failed
        totalTasksQueued.value = response.data.data.statuses.queued
        totalTasksDone.value = response.data.data.statuses.done

        showSuccessNotification(response.data.message)

        return response.data
      })
      .finally(() => {
        isLoading.value = false
      })
  })

  /**
   * Получает детальную информацию о задаче
   */
  const fetchTaskDetails = useApi(async (parameters?: { taskId: number }) => {
    if (!parameters) throw new Error('Не указан ID задачи')

    isTaskDetailsLoading.value = parameters.taskId

    return axios.get<TaskDetailsResponse>(`tasks/task-info/${parameters.taskId}`)
      .then(response => {
        taskDetails.value = response.data.data
        return response.data
      })
      .finally(() => {
        isTaskDetailsLoading.value = null
      })
  })

  /**
   * Получает количество задач по статусам
   */
  const getTasksCountByStatus = useApi(async (parameters?: {
    status?: TaskStatus
    accountId?: string | number
  }) => {
    const { status = '', accountId = '' } = parameters || {}
    const url = `tasks/count-by-status${status ? `/${status}` : ''}${accountId ? `/${accountId}` : ''}`

    return axios.get<TaskCountByStatusResponse>(url)
      .then(response => {
        taskCountByStatus.value = response.data.data
        showSuccessNotification(response.data.message)

        return response.data
      })
      .catch(error => {
        showErrorNotification((error as Error)?.message || 'Произошла ошибка при получении количества задач')
        throw error
      })
  })

  /**
   * Удаляет лайк задачи
   */
  const deleteLike = useApi(async (parameters?: { taskId: number }) => {
    if (!parameters) throw new Error('Не указан ID задачи')

    return axios.delete<DeleteLikeResponse>(`tasks/delete-like/${parameters.taskId}`)
      .then(async response => {
        await fetchTaskDetails.execute({ taskId: parameters.taskId })
        showSuccessNotification(response.data.message)
        return response.data
      })
      .catch(error => {
        showErrorNotification((error as Error)?.message || 'Произошла ошибка при удалении лайка')
        throw error
      })
  })

  /**
   * Удаляет задачу по ID
   */
  const deleteTask = useApi(async (parameters?: { id: number, }) => {
    if (!parameters) throw new Error('Не указан ID задачи')

    return axios.delete<DeleteTaskResponse>(`tasks/delete-task-by-id/${parameters.id}`)
      .then(async response => {
        showSuccessNotification(response.data.message)

        return response.data
      })
  })

  /**
   * Удаляет одну задачу по ID (упрощенная версия)
   */
  const deleteSingleTaskById = useApi(async (parameters?: { id: number }) => {
    if (!parameters) throw new Error('Не указан ID задачи')

    return axios.delete<DeleteTaskResponse>(`tasks/delete-task-by-id/${parameters.id}`)
      .then(response => {
        tasks.value = tasks.value.filter(task => task.id !== parameters.id)
        return response.data
      })
  })

  /**
   * Удаляет все задачи по статусу и аккаунту
   */
  const deleteAllTasks = useApi(async (parameters?: {
    status?: TaskStatus
    accountId?: string | number
  }) => {
    const { status, accountId } = parameters || {}

    // Проверяем, определены ли параметры status и accountId
    const statusPart = status ? `/${status}` : '/null'
    const accountIdPart = accountId ? `/${accountId}` : ''

    const url = `tasks/delete-all-tasks${statusPart}${accountIdPart}`

    return axios.delete<DeleteTaskResponse>(url)
      .then(response => {
        tasks.value = []

        // Обновляем счетчики
        if (status) {
          switch (status) {
            case 'failed':
              totalTasksFailed.value = 0
              break
            case 'queued':
              totalTasksQueued.value = 0
              break
            case 'done':
              totalTasksDone.value = 0
              break
          }
        } else {
          totalTasksCount.value = 0
          totalTasksFailed.value = 0
          totalTasksQueued.value = 0
          totalTasksDone.value = 0
        }

        showSuccessNotification(response.data.message)
        return response.data
      })
      .then(async (data) => {
        await fetchTasks.execute({ status, accountId })
        return data
      })
      .catch(error => {
        showErrorNotification((error as Error)?.message || 'Произошла ошибка при удалении задач')
        throw error
      })
  })

  /**
   * Создает задачи лайков для постов из новостной ленты
   */
  const createAndQueueLikeTasksFromNewsfeed = useApi(async (parameters?: CreateTasksRequest) => {
    return axios.post<TasksListResponse>('tasks/get-posts-for-like', parameters)
      .then(response => {
        showSuccessNotification(response.data.message)
        return response.data
      })
      .catch(error => {
        showErrorNotification((error as Error)?.message || 'Произошла ошибка при создании задач лайков из новостной ленты')
        throw error
      })
  })

  /**
   * Обрабатывает и ставит в очередь задачи лайков
   */
  const processAndQueuePendingLikeTasks = useApi(async (parameters?: CreateTasksRequest) => {
    return axios.post<TasksListResponse>('tasks/add-task-likes', parameters)
      .then(response => {
        showSuccessNotification(response.data.message)
        return response.data
      })
      .catch(error => {
        showErrorNotification((error as Error)?.message || 'Произошла ошибка при обработке задач лайков')
        throw error
      })
  })

  /**
   * Создает задачи лайков для постов пользователей
   */
  const createLikeTasksForUserWallPosts = useApi(async (parameters?: CreateTasksRequest) => {
    return axios.post<TasksListResponse>('tasks/create-for-users', parameters)
      .then(response => {
        showSuccessNotification(response.data.message)
        return response.data
      })
      .catch(error => {
        showErrorNotification((error as Error)?.message || 'Произошла ошибка при создании задач лайков для постов пользователя')
        throw error
      })
  })

  // Геттеры
  const getTaskById = computed(() => {
    return (taskId: number) => tasks.value.find(task => task.id === taskId)
  })

  const isUserLiked = computed(() => {
    return (taskId: number) => {
      const task = tasks.value.find(task => task.id === taskId)
      if (taskDetails.value?.liked_users && task?.account_id) {
        return taskDetails.value.liked_users.some(user => user.id === task.account_id)
      }
      return false
    }
  })

  return {
    // State
    tasks,
    totalTasksCount,
    totalTasksQueued,
    totalTasksFailed,
    totalTasksDone,
    taskCountByStatus,
    isLoading,
    isTaskDetailsLoading,
    taskDetails,

    // Actions
    fetchTasks,
    fetchTaskDetails,
    getTasksCountByStatus,
    deleteLike,
    deleteTask,
    deleteSingleTaskById,
    deleteAllTasks,
    createAndQueueLikeTasksFromNewsfeed,
    processAndQueuePendingLikeTasks,
    createLikeTasksForUserWallPosts,

    // Getters
    getTaskById,
    isUserLiked
  }
})
