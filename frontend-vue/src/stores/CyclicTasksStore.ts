import { defineStore } from 'pinia'
import axios from '@/helpers/axiosConfig'
import useApi from '@/composables/useApi'
import type {
  CreateCyclicTaskRequest,
  EditCyclicTaskRequest,
  CyclicTasksListResponse,
  CreateCyclicTaskResponse,
  EditCyclicTaskResponse,
  DeleteCyclicTaskResponse,
  PauseCyclicTaskResponse
} from '@/models/CyclicTaskModel'
import type { ApiResponseWrapper } from '@/models/ApiModel'

export const useCyclicTasksStore = defineStore('cyclicTasks', () => {
  /**
   * Получает список всех циклических задач
   */
  const fetchCyclicTasks = useApi(async () => {
    return (await axios.get<ApiResponseWrapper<CyclicTasksListResponse>>('cyclic-tasks')).data
  })

  /**
   * Создает новую циклическую задачу
   */
  const createCyclicTask = useApi(async (parameters?: CreateCyclicTaskRequest) => {
    if (!parameters) throw new Error('Не указаны параметры для создания циклической задачи')
    return (await axios.post<ApiResponseWrapper<CreateCyclicTaskResponse>>('cyclic-tasks/create-cyclic-task', parameters)).data
  })

  /**
   * Редактирует циклическую задачу
   */
  const editCyclicTask = useApi(async (parameters?: { taskId: number; taskData: EditCyclicTaskRequest }) => {
    if (!parameters) throw new Error('Не указаны параметры для редактирования циклической задачи')
    return (await axios.patch<ApiResponseWrapper<EditCyclicTaskResponse>>(`cyclic-tasks/${parameters.taskId}`, parameters.taskData)).data
  })

  /**
   * Удаляет циклическую задачу по ID
   */
  const deleteCyclicTask = useApi(async (parameters?: { taskId: number }) => {
    if (!parameters) throw new Error('Не указан ID циклической задачи')
    return (await axios.delete<ApiResponseWrapper<DeleteCyclicTaskResponse>>(`cyclic-tasks/${parameters.taskId}`)).data
  })

  /**
   * Удаляет все циклические задачи
   */
  const deleteAllCyclicTasks = useApi(async () => {
    return (await axios.delete<ApiResponseWrapper<DeleteCyclicTaskResponse>>('cyclic-tasks/delete-all-cyclic-tasks')).data
  })

  /**
   * Приостанавливает/возобновляет циклическую задачу
   */
  const pauseCyclicTask = useApi(async (parameters?: { taskId: number }) => {
    if (!parameters) throw new Error('Не указан ID циклической задачи')
    return (await axios.patch<ApiResponseWrapper<PauseCyclicTaskResponse>>(`cyclic-tasks/pause-cyclic-task/${parameters.taskId}`)).data
  })

  return {
    fetchCyclicTasks,
    createCyclicTask,
    editCyclicTask,
    deleteCyclicTask,
    deleteAllCyclicTasks,
    pauseCyclicTask
  }
})
