import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from '@/helpers/axiosConfig'
import { showErrorNotification, showSuccessNotification } from '@/helpers/notyfHelper'
import useApi from '@/composables/useApi'
import type {
  CyclicTask,
  CyclicTasksListResponse,
  CyclicTaskResponse,
  DeleteCyclicTaskResponse,
  CreateCyclicTaskRequest,
  EditCyclicTaskRequest
} from '@/models/CyclicTaskModel'
import type { ApiResponseWrapper } from '@/models/ApiModel'

export const useCyclicTasksStore = defineStore('cyclicTasks', () => {
  // State
  const cyclicTasks = ref<CyclicTask[]>([])
  const isLoading = ref<boolean>(false)
  const cyclicTasksPerPage = ref<number>(30)
  const totalCyclicTasksCount = ref<number | null>(null)
  const deletedCyclicTasksCount = ref<number>(0)

  /**
   * Получает список циклических задач с пагинацией
   */
  const fetchCyclicTasks = useApi(async (parameters?: { page?: number }) => {
    const { page = 1 } = parameters || {}
    
    isLoading.value = true

    // Инициализируем переменную effectivePerPage значением cyclicTasksPerPage.
    // Это количество задач, запрашиваемых с сервера за один раз.
    let effectivePerPage = cyclicTasksPerPage.value

    // Используем Nullish coalescing operator для проверки totalCyclicTasksCount на null/undefined.
    // Если totalCyclicTasksCount не определено, используем значение 0.
    const totalCyclicTasksCountValue = totalCyclicTasksCount.value ?? 0

    // Вычисляем adjustedTotal, добавляя к totalCyclicTasksCount количество удаленных задач.
    // Это необходимо для корректировки пагинации с учетом недавно удаленных задач.
    const adjustedTotal = totalCyclicTasksCountValue + deletedCyclicTasksCount.value

    // Рассчитываем общее количество страниц, разделив adjustedTotal на количество задач на странице.
    const totalPages = Math.ceil(adjustedTotal / cyclicTasksPerPage.value)

    // Если текущая страница не первая, проверяем, нужно ли корректировать effectivePerPage.
    if (page > 1) {
      // Если текущая страница меньше общего количества страниц, значит это не последняя страница.
      if (page < totalPages) {
        // Для не последних страниц увеличиваем effectivePerPage на количество удаленных задач,
        // чтобы компенсировать удаление и заполнить страницу полностью.
        effectivePerPage += deletedCyclicTasksCount.value
      } else {
        // Если это последняя страница, вычисляем количество задач, которые должны быть на этой странице.
        // Это делается путем вычитания из adjustedTotal количества задач на предыдущих страницах.
        const tasksLeftForLastPage = adjustedTotal - (cyclicTasksPerPage.value * (page - 1))

        // Корректируем effectivePerPage, чтобы на последней странице было не больше задач, чем осталось.
        // Используем Math.min для выбора меньшего из двух значений: расчетного количества задач
        // на последней странице и effectivePerPage с учетом удаленных задач.
        if (deletedCyclicTasksCount.value > 0) {
          effectivePerPage = Math.min(tasksLeftForLastPage, effectivePerPage + deletedCyclicTasksCount.value)
        }
      }
    }

    return axios.get<CyclicTasksListResponse>(`cyclic-tasks?page=${page}&perPage=${effectivePerPage}`)
      .then(response => {
        if (page === 1) {
          cyclicTasks.value = response.data.data.data
          // Обновляем общее количество при первой загрузке
          totalCyclicTasksCount.value = response.data.data.pagination.total
        } else {
          cyclicTasks.value = [...cyclicTasks.value, ...response.data.data.data]
        }

        // Сбрасываем счетчик удаленных задач после каждого запроса
        deletedCyclicTasksCount.value = 0
        showSuccessNotification(response.data.message)
        
        return response.data
      })
      .catch(error => {
        showErrorNotification((error as Error)?.message || 'Произошла ошибка при получении циклических задач')
        throw error
      })
      .finally(() => {
        isLoading.value = false
      })
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

        totalCyclicTasksCount.value = totalCyclicTasksCount.value && totalCyclicTasksCount.value > 0 ? totalCyclicTasksCount.value - 1 : 0

        // Увеличиваем счетчик удаленных задач
        deletedCyclicTasksCount.value++
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
        totalCyclicTasksCount.value = null
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
    isLoading,
    cyclicTasksPerPage,
    totalCyclicTasksCount,
    deletedCyclicTasksCount,
    
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
