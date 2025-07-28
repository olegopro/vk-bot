import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from '@/helpers/axiosConfig'
import { showErrorNotification, showSuccessNotification } from '@/helpers/notyfHelper'
import useApi from '@/composables/useApi'
import { Nullable } from '@/types'
import {
  Task,
  TaskDetails,
  TaskStatuses,
  TasksListResponse,
  TaskDetailsResponse,
  TaskCountByStatusResponse,
  DeleteTaskResponse,
  DeleteLikeResponse,
  TaskStatus
} from '@/models/TaskModel'

export const useTasksStore = defineStore('tasks', () => {
  const tasks = ref<Task[]>([])
  const taskCountByStatus = ref<Nullable<TaskStatuses>>(null)
  const isLoading = ref<boolean>(false)
  const isTaskDetailsLoading = ref<Nullable<number>>(null)
  const taskDetails = ref<Nullable<TaskDetails>>(null)

  /**
   * Получает список задач
   */
  const fetchTasks = useApi(async (parameters?: {
    status?: TaskStatus
    accountId?: string | number
  }) => {
    const { status = '', accountId = '' } = parameters || {}

    const url = `tasks${status ? `/${status}` : ''}${accountId ? `/${accountId}` : ''}`

    return (await axios.get<TasksListResponse>(url)).data
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

    return (await axios.delete<DeleteTaskResponse>(url)).data
  })

  /**
   * Создает задачи лайков для постов из новостной ленты
   */
  // TODO: Неиспользуемый метод
  // const createAndQueueLikeTasksFromNewsfeed = useApi(async (parameters?: CreateTasksRequest) => {
  //   return axios.post<TasksListResponse>('tasks/get-posts-for-like', parameters)
  //     .then(response => {
  //       showSuccessNotification(response.data.message)
  //       return response.data
  //     })
  //     .catch(error => {
  //       showErrorNotification((error as Error)?.message || 'Произошла ошибка при создании задач лайков из новостной ленты')
  //       throw error
  //     })
  // })

  /**
   * Обрабатывает и ставит в очередь задачи лайков
   */
  // TODO: Неиспользуемый метод
  // const processAndQueuePendingLikeTasks = useApi(async (parameters?: CreateTasksRequest) => {
  //   return axios.post<TasksListResponse>('tasks/add-task-likes', parameters)
  //     .then(response => {
  //       showSuccessNotification(response.data.message)
  //       return response.data
  //     })
  //     .catch(error => {
  //       showErrorNotification((error as Error)?.message || 'Произошла ошибка при обработке задач лайков')
  //       throw error
  //     })
  // })

  // TODO: Неиспользуемый метод
  /**
   * Создает задачи лайков для постов пользователей
   */
  // const createLikeTasksForUserWallPosts = useApi(async (parameters?: CreateTasksRequest) => {
  //   return axios.post<TasksListResponse>('tasks/create-for-users', parameters)
  //     .then(response => {
  //       showSuccessNotification(response.data.message)
  //       return response.data
  //     })
  //     .catch(error => {
  //       showErrorNotification((error as Error)?.message || 'Произошла ошибка при создании задач лайков для постов пользователя')
  //       throw error
  //     })
  // })

  // Геттер
  // TODO: Неиспользуемый метод
  // const getTaskById = computed(() => {
  //   return (taskId: number) => tasks.value.find(task => task.id === taskId)
  // })

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

    // Getters
    isUserLiked
  }
})
