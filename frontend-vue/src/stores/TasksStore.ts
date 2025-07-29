import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from '@/helpers/axiosConfig'
import { showErrorNotification, showSuccessNotification } from '@/helpers/notyfHelper'
import useApi from '@/composables/useApi'
import { Nullable } from '@/types'
import {
  TaskDetails,
  TasksListData,
  TaskStatus
} from '@/models/TaskModel'
import { ApiResponseWrapper } from '@/models/ApiModel'

export const useTasksStore = defineStore('tasks', () => {
  const taskDetails = ref<Nullable<TaskDetails>>(null)

  /**
   * Получает список задач
   */
  const fetchTasks = useApi(async (parameters?: { status?: TaskStatus, accountId?: string }) => {
    const urlParts = ['tasks', parameters?.status, parameters?.accountId].filter(Boolean)
    return (await axios.get<ApiResponseWrapper<TasksListData>>(urlParts.join('/'))).data
  })

  /**
   * Вычисляемое свойство для получения списка задач
   */
  const tasks = computed(() => fetchTasks.data.value?.tasks || [])

  /**
   * Получает детальную информацию о задаче
   */
  const fetchTaskDetails = useApi(async (parameters?: { taskId: number }) => {
    if (!parameters) throw new Error('Не указан ID задачи')

    return axios.get<ApiResponseWrapper<TaskDetails>>(`tasks/task-info/${parameters.taskId}`)
      .then(response => {
        taskDetails.value = response.data.data
        return response.data
      })
  })

  /**
   * Удаляет лайк задачи
   */
  const deleteLike = useApi(async (parameters?: { taskId: number }) => {
    if (!parameters) throw new Error('Не указан ID задачи')

    return axios.delete<ApiResponseWrapper<null>>(`tasks/delete-like/${parameters.taskId}`)
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
    return (await (axios.delete<ApiResponseWrapper<null>>(`tasks/delete-task-by-id/${parameters.id}`))).data
  })

  /**
   * Удаляет все задачи по статусу и аккаунту
   */
  const deleteAllTasks = useApi(async (parameters?: {status?: TaskStatus, accountId?: string | number
  }) => {
    const urlParts = ['tasks/delete-all-tasks', parameters?.status, parameters?.accountId].filter(Boolean)
    return (await axios.delete<ApiResponseWrapper<null>>(urlParts.join('/'))).data
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
    taskDetails,

    // Actions
    fetchTasks,
    fetchTaskDetails,
    deleteLike,
    deleteTask,
    deleteAllTasks,

    // Getters
    isUserLiked
  }
})
