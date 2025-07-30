import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from '@/helpers/axiosConfig'
import { showErrorNotification, showSuccessNotification } from '@/helpers/notyfHelper'
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
  const cyclicTasks = ref<CyclicTask[]>([])

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

    return axios.post<ApiResponseWrapper<any>>('cyclic-tasks/create-cyclic-task', {
      account_id: parameters.account_id,
      tasks_per_hour: parameters.tasks_per_hour,
      total_task_count: parameters.total_task_count,
      status: parameters.status,
      selected_times: parameters.selected_times
    })
      .then(response => {
        showSuccessNotification(response.data.message)
        return response.data
      })
      .catch(error => {
        showErrorNotification((error as Error)?.message || 'Произошла ошибка при создании циклической задачи')
        throw error
      })
  })

  /**
   * Редактирует циклическую задачу
   */
  const editCyclicTask = useApi(async (parameters?: { taskId: number; taskData: EditCyclicTaskRequest }) => {
    if (!parameters) throw new Error('Не указаны параметры для редактирования циклической задачи')

    return axios.patch<CyclicTaskResponse>(`cyclic-tasks/${parameters.taskId}`, parameters.taskData)
      .then(response => {
        // Сервер возвращает обновлённую задачу в ответе
        const updatedTask = response.data.data

        // Находим индекс задачи в массиве
        const index = cyclicTasks.value.findIndex(task => task.id === parameters.taskId)

        // Если задача найдена, обновляем её данные
        if (index !== -1) cyclicTasks.value[index] = updatedTask

        showSuccessNotification(response.data.message)
        return response.data
      })
      .catch(error => {
        showErrorNotification((error as Error)?.message || 'Произошла ошибка при редактировании циклической задачи')
        throw error
      })
  })

  /**
   * Удаляет циклическую задачу по ID
   */
  const deleteCyclicTask = useApi(async (parameters?: { taskId: number }) => {
    if (!parameters) throw new Error('Не указан ID циклической задачи')

    return axios.delete<DeleteCyclicTaskResponse>(`cyclic-tasks/${parameters.taskId}`)
      .then(response => {
        // Удаляем задачу из списка
        const index = cyclicTasks.value.findIndex(cyclicTask => cyclicTask.id === parameters.taskId)
        if (index !== -1) cyclicTasks.value.splice(index, 1)

        showSuccessNotification(response.data.message)

        return response.data
      })
      .catch(error => {
        showErrorNotification((error as Error)?.message || 'Произошла ошибка при удалении циклической задачи')
        throw error
      })
  })

  /**
   * Удаляет все циклические задачи
   */
  const deleteAllCyclicTasks = useApi(async () => {
    return axios.delete<DeleteCyclicTaskResponse>('cyclic-tasks/delete-all-cyclic-tasks')
      .then(response => {
        cyclicTasks.value = []
        showSuccessNotification(response.data.message)

        return response.data
      })
      .catch(error => {
        showErrorNotification((error as Error)?.message || 'Произошла ошибка при удалении всех циклических задач')
        throw error
      })
  })

  /**
   * Приостанавливает/возобновляет циклическую задачу
   */
  const pauseCyclicTask = useApi(async (parameters?: { taskId: number }) => {
    if (!parameters) throw new Error('Не указан ID циклической задачи')

    return axios.patch<ApiResponseWrapper<any>>(`cyclic-tasks/pause-cyclic-task/${parameters.taskId}`)
      .then(response => {
        const taskIndex = cyclicTasks.value.findIndex(task => task.id === parameters.taskId)
        if (taskIndex !== -1) {
          cyclicTasks.value[taskIndex].status = cyclicTasks.value[taskIndex].status === 'active' ? 'pause' : 'active'
        }

        showSuccessNotification(response.data.message)
        return response.data
      })
      .catch(error => {
        showErrorNotification((error as Error)?.message || 'Произошла ошибка при изменении статуса циклической задачи')
        throw error
      })
  })

  // Геттеры
  const getTaskById = computed(() => {
    return (taskId: number) => cyclicTasks.value.find(task => task.id === taskId)
  })

  return {
    // State
    cyclicTasks,

    // Actions
    fetchCyclicTasks,
    createCyclicTask,
    editCyclicTask,
    deleteCyclicTask,
    deleteAllCyclicTasks,
    pauseCyclicTask,

    // Getters
    getTaskById
  }
})
