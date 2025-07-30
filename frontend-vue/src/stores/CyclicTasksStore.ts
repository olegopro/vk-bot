import { defineStore } from 'pinia'
import axios from '@/helpers/axiosConfig'
import useApi from '@/composables/useApi'
import type {
  CyclicTask,
  CyclicTaskResponse,
  DeleteCyclicTaskResponse,
  CreateCyclicTaskRequest,
  EditCyclicTaskRequest
} from '@/models/CyclicTaskModel'
import type { ApiResponseWrapper } from '@/models/ApiModel'

export const useCyclicTasksStore = defineStore('cyclicTasks', () => {
  /**
   * Получает список всех циклических задач
   */
  const fetchCyclicTasks = useApi(async () => {
    return (await axios.get<ApiResponseWrapper<CyclicTask[]>>('cyclic-tasks')).data
  })

  /**
   * Создает новую циклическую задачу
   */
  const createCyclicTask = useApi(async (parameters?: CreateCyclicTaskRequest) => {
    if (!parameters) throw new Error('Не указаны параметры для создания циклической задачи')

    return (await axios.post<ApiResponseWrapper<any>>('cyclic-tasks/create-cyclic-task', {
      account_id: parameters.account_id,
      tasks_per_hour: parameters.tasks_per_hour,
      total_task_count: parameters.total_task_count,
      status: parameters.status,
      selected_times: parameters.selected_times
    })).data
  })

  /**
   * Редактирует циклическую задачу
   */
  const editCyclicTask = useApi(async (parameters?: { taskId: number; taskData: EditCyclicTaskRequest }) => {
    if (!parameters) throw new Error('Не указаны параметры для редактирования циклической задачи')
    return (await axios.patch<CyclicTaskResponse>(`cyclic-tasks/${parameters.taskId}`, parameters.taskData)).data
  })

  /**
   * Удаляет циклическую задачу по ID
   */
  const deleteCyclicTask = useApi(async (parameters?: { taskId: number }) => {
    if (!parameters) throw new Error('Не указан ID циклической задачи')
    return (await axios.delete<DeleteCyclicTaskResponse>(`cyclic-tasks/${parameters.taskId}`)).data
  })

  /**
   * Удаляет все циклические задачи
   */
  const deleteAllCyclicTasks = useApi(async () => {
    return (await axios.delete<DeleteCyclicTaskResponse>('cyclic-tasks/delete-all-cyclic-tasks')).data
  })

  /**
   * Приостанавливает/возобновляет циклическую задачу
   */
  const pauseCyclicTask = useApi(async (parameters?: { taskId: number }) => {
    if (!parameters) throw new Error('Не указан ID циклической задачи')

    return (await axios.patch<ApiResponseWrapper<any>>(`cyclic-tasks/pause-cyclic-task/${parameters.taskId}`)).data
  })

  return {
    // Actions
    fetchCyclicTasks,
    createCyclicTask,
    editCyclicTask,
    deleteCyclicTask,
    deleteAllCyclicTasks,
    pauseCyclicTask
  }
})
