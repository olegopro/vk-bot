import { defineStore } from 'pinia'
import axios from '@/helpers/axiosConfig'
import { showErrorNotification, showSuccessNotification } from '@/helpers/notyfHelper'

export const useTasksStore = defineStore('tasks', {
  state: () => ({
    tasks: [],
    totalTasksCount: null,
    totalTasksQueued: null,
    totalTasksFailed: null,
    totalTasksDone: null,
    taskCountByStatus: null,
    isLoading: false,
    isTaskDetailsLoading: null,
    taskDetails: null,
    tasksPerPage: 30,
    deletedTasksCount: 0
  }),

  actions: {
    async fetchTasks(status = '', accountId = '', page = 1) {
      this.isLoading = true

      // Инициализируем переменную effectivePerPage значением tasksPerPage.
      let effectivePerPage = this.tasksPerPage

      let totalTasksCountByStatus = 0
      switch (status) {
        case 'done':
          totalTasksCountByStatus = this.totalTasksDone ?? 0
          break

        case 'failed':
          totalTasksCountByStatus = this.totalTasksFailed ?? 0
          break

        case 'queued':
          totalTasksCountByStatus = this.totalTasksQueued ?? 0
          break

        default:
          totalTasksCountByStatus = this.totalTasksCount ?? 0
      }

      // Вычисляем adjustedTotal, добавляя к totalTasksCountByStatus количество удаленных задач.
      // Это необходимо для корректировки пагинации с учетом недавно удаленных задач.
      const adjustedTotal = totalTasksCountByStatus + this.deletedTasksCount

      // Рассчитываем общее количество страниц, разделив adjustedTotal на количество задач на странице.
      const totalPages = Math.ceil(adjustedTotal / this.tasksPerPage)

      // Если текущая страница не первая, проверяем, нужно ли корректировать effectivePerPage.
      if (page > 1) {
        // Если текущая страница меньше общего количества страниц, значит это не последняя страница.
        if (page < totalPages) {
          // Для не последних страниц увеличиваем effectivePerPage на количество удаленных задач,
          // чтобы компенсировать удаление и заполнить страницу полностью.
          effectivePerPage += this.deletedTasksCount
        } else {
          // Если это последняя страница, вычисляем количество задач, которые должны быть на этой странице.
          // Это делается путем вычитания из adjustedTotal количества задач на предыдущих страницах.
          const tasksLeftForLastPage = adjustedTotal - (this.tasksPerPage * (page - 1))

          // Корректируем effectivePerPage, чтобы на последней странице было не больше задач, чем осталось.
          // Используем Math.min для выбора меньшего из двух значений: расчетного количества задач
          // на последней странице и effectivePerPage с учетом удаленных задач.
          if (this.deletedTasksCount > 0) {
            effectivePerPage = Math.min(tasksLeftForLastPage, effectivePerPage + this.deletedTasksCount)
          }
        }
      }

      /*
                Формирование базового URL для запроса.
                Если параметр status задан (не пустая строка), то он добавляется к URL.
                Если параметр accountId задан, то он также добавляется к URL.
                Например, если status = 'failed' и accountId = '123', URL станет 'tasks/failed/123'.
                Если оба параметра не заданы, URL останется 'tasks'.
            */
      const url = `tasks${status ? `/${status}` : ''}${accountId ? `/${accountId}` : ''}?page=${page}&perPage=${effectivePerPage}`

      await axios.get(url).then(({ data }) => {
        if (page === 1) {
          this.tasks = data.data.tasks.data
          this.totalTasksCount = data.data.total
          this.totalTasksFailed = data.data.statuses.failed
          this.totalTasksQueued = data.data.statuses.queued
          this.totalTasksDone = data.data.statuses.done
        } else {
          this.tasks = [...this.tasks, ...data.data.tasks.data]
        }

        this.deletedTasksCount = 0
        showSuccessNotification(data.message)
      })
        .finally(() => this.isLoading = false)
    },

    async fetchTaskDetails(taskId) {
      this.isTaskDetailsLoading = taskId
      await axios.get(`tasks/task-info/${taskId}`)
        .then(({ data }) => this.taskDetails = data.data)
        .catch(error => {
          throw error
        })
        .finally(() => this.isTaskDetailsLoading = null)
    },

    async getTasksCountByStatus(status = '', accountId = '') {
      const url = `tasks/count-by-status${status ? `/${status}` : ''}${accountId ? `/${accountId}` : ''}`

      await axios.get(url)
        .then(({ data }) => {
          this.taskCountByStatus = data.data
          showSuccessNotification(data.message)
        })
    },

    async deleteLike(taskId) {
      await axios.delete(`tasks/delete-like/${taskId}`)
        .then(({ data }) => {
          this.fetchTaskDetails(taskId)
          showSuccessNotification(data.message)
        })
    },

    async deleteTask(id) {
      await axios.delete(`tasks/delete-task-by-id/${id}`)
        .then(({ data }) => {
          const task = this.tasks.find(task => task.id === id)

          // Удаляем задачу из списка задач
          const index = this.tasks.findIndex(task => task.id === id)
          if (index !== -1) this.tasks.splice(index, 1)

          // Уменьшаем общий счетчик задач
          this.totalTasksCount = this.totalTasksCount > 0 ? this.totalTasksCount - 1 : 0

          // Обновляем счетчики по статусам задач
          switch (task.status) {
            case 'failed':
              this.totalTasksFailed = this.totalTasksFailed > 0 ? this.totalTasksFailed - 1 : 0
              break

            case 'queued':
              this.totalTasksQueued = this.totalTasksQueued > 0 ? this.totalTasksQueued - 1 : 0
              break

            case 'done':
              this.totalTasksDone = this.totalTasksDone > 0 ? this.totalTasksDone - 1 : 0
              break

            default:
              // Здесь можно обработать другие статусы, если они есть
              break
          }

          // Увеличиваем счетчик удаленных задач
          this.deletedTasksCount++

          // Показываем уведомление об успешном удалении
          showSuccessNotification(data.message)
        })
        .catch(error => {
          // Показываем уведомление об ошибке, если что-то пошло не так
          showErrorNotification(error || 'Произошла ошибка при удалении задачи')
        })
    },

    async deleteSingleTaskById(id) {
      await axios.delete(`tasks/delete-task-by-id/${id}`)
        .then(() => this.tasks = this.tasks.filter(task => task.id !== id))
    },

    async deleteAllTasks(status, accountId) {
      // Проверяем, определены ли параметры status и accountId
      const statusPart = status ? `/${status}` : '/null' // Если status не определен, используем '/null'
      const accountIdPart = accountId ? `/${accountId}` : '' // Если accountId не определен, не добавляем его в URL

      const url = `tasks/delete-all-tasks${statusPart}${accountIdPart}`

      await axios.delete(url)
        .then(({ data }) => {
          this.tasks = []

          this.fetchTasks(status, accountId)
          showSuccessNotification(data.message)
        })
    }
  },

  getters: {
    getTaskById: (state) => (taskId) => state.tasks.find(task => task.id === taskId),

    isUserLiked: (state) => (taskId) => {
      const task = state.tasks.find(task => task.id === taskId)
      if (state.taskDetails && state.taskDetails.liked_users && task && task.account_id) {
        return state.taskDetails.liked_users.some(user => user.id === task.account_id)
      }

      return false
    }
  },
})
