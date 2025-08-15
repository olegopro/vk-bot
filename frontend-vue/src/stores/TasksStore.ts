import { defineStore } from 'pinia'
import axios from '@/helpers/axiosConfig'
import useApi from '@/composables/useApi'
import type { 
  TaskStatus,
  TasksListResponse,
  TaskDetailsResponse,
  DeleteLikeResponse,
  DeleteTaskResponse,
  DeleteAllTasksResponse
} from '@/models/TaskModel'
import { ApiResponseWrapper } from '@/models/ApiModel'

export const useTasksStore = defineStore('tasks', () => {
  /**
   * Получает список задач
   */
  const fetchTasks = useApi(async (parameters?: { status?: TaskStatus, accountId?: string }) => {
    const urlParts = ['tasks', parameters?.status, parameters?.accountId].filter(Boolean)
    return (await axios.get<ApiResponseWrapper<TasksListResponse>>(urlParts.join('/'))).data
  })

  /**
   * Получает детальную информацию о задаче
   */
  const fetchTaskDetails = useApi(async (parameters?: { taskId: number }) => {
    if (!parameters) throw new Error('Не указан ID задачи')
    return (await axios.get<ApiResponseWrapper<TaskDetailsResponse>>(`tasks/task-info/${parameters.taskId}`)).data
  })

  /**
   * Удаляет лайк задачи
   */
  const deleteLike = useApi(async (parameters?: { taskId: number }) => {
    if (!parameters) throw new Error('Не указан ID задачи')
    return (await axios.delete<ApiResponseWrapper<DeleteLikeResponse>>(`tasks/delete-like/${parameters.taskId}`)).data
  })

  /**
   * Удаляет задачу по ID
   */
  const deleteTask = useApi(async (parameters?: { taskId: number }) => {
    if (!parameters) throw new Error('Не указан ID задачи')
    return (await (axios.delete<ApiResponseWrapper<DeleteTaskResponse>>(`tasks/delete-task-by-id/${parameters.taskId}`))).data
  })

  /**
   * Удаляет все задачи по статусу и аккаунту
   */
  const deleteAllTasks = useApi(async (parameters?: { status?: TaskStatus, accountId?: string | number }) => {
    const urlParts = ['tasks/delete-all-tasks', parameters?.status, parameters?.accountId].filter(Boolean)
    return (await axios.delete<ApiResponseWrapper<DeleteAllTasksResponse>>(urlParts.join('/'))).data
  })

  return {
    fetchTasks,
    fetchTaskDetails,
    deleteLike,
    deleteTask,
    deleteAllTasks
  }
})
