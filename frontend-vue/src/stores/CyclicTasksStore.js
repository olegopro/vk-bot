import { defineStore } from 'pinia'
import axios from 'axios'
import { showErrorNotification, showSuccessNotification } from '@/helpers/notyfHelper'

export const useCyclicTasksStore = defineStore('cyclicTasks', {
    state: () => ({
        cyclicTasks: [],
        isLoading: false,
        cyclicTasksPerPage: 30,
        totalCyclicTasksCount: null,
        deletedCyclicTasksCount: 0
    }),

    actions: {
        async fetchCyclicTasks(page = 1) {
            this.isLoading = true

            // Инициализируем переменную effectivePerPage значением tasksPerPage.
            // Это количество задач, запрашиваемых с сервера за один раз.
            let effectivePerPage = this.cyclicTasksPerPage

            // Используем Nullish coalescing operator для проверки totalTasksCount на null/undefined.
            // Если totalTasksCount не определено, используем значение 0.
            const totalCyclicTasksCount = this.totalCyclicTasksCount ?? 0

            // Вычисляем adjustedTotal, добавляя к totalTasksCount количество удаленных задач.
            // Это необходимо для корректировки пагинации с учетом недавно удаленных задач.
            const adjustedTotal = totalCyclicTasksCount + this.deletedCyclicTasksCount
            console.log('adjustedTotal', adjustedTotal)

            // Рассчитываем общее количество страниц, разделив adjustedTotal на количество задач на странице.
            const totalPages = Math.ceil(adjustedTotal / this.cyclicTasksPerPage)
            console.log('totalPages', totalPages)

            // Если текущая страница не первая, проверяем, нужно ли корректировать effectivePerPage.
            if (page > 1) {
                // Если текущая страница меньше общего количества страниц, значит это не последняя страница.
                if (page < totalPages) {
                    // Для не последних страниц увеличиваем effectivePerPage на количество удаленных задач,
                    // чтобы компенсировать удаление и заполнить страницу полностью.
                    effectivePerPage += this.deletedCyclicTasksCount
                    console.log('effectivePerPage', effectivePerPage)
                } else {
                    // Если это последняя страница, вычисляем количество задач, которые должны быть на этой странице.
                    // Это делается путем вычитания из adjustedTotal количества задач на предыдущих страницах.
                    const tasksLeftForLastPage = adjustedTotal - (this.cyclicTasksPerPage * (page - 1))
                    console.log('tasksLeftForLastPage', tasksLeftForLastPage)

                    // Корректируем effectivePerPage, чтобы на последней странице было не больше задач, чем осталось.
                    // Используем Math.min для выбора меньшего из двух значений: расчетного количества задач
                    // на последней странице и effectivePerPage с учетом удаленных задач.
                    if (this.deletedTasksCount > 0) {
                        effectivePerPage = Math.min(tasksLeftForLastPage, effectivePerPage + this.deletedTasksCount)
                    }
                }
            }

            console.log('effectivePerPage', effectivePerPage)
            await axios.get(`http://localhost:8080/api/cyclic-tasks?page=${page}&perPage=${effectivePerPage}`)
                .then(({ data }) => {
                    if (page === 1) {
                        this.cyclicTasks = data.data
                        // Обновляем общее количество при первой загрузке
                        this.totalCyclicTasksCount = data.pagination.total
                    } else {
                        this.cyclicTasks = [...this.cyclicTasks, ...data.data]
                    }

                    // Сбрасываем счетчик удаленных задач после каждого запроса
                    this.deletedCyclicTasksCount = 0
                    showSuccessNotification(data.message)
                })
                .catch(error => showErrorNotification(error))
                .finally(() => this.isLoading = false)
        },

        async createCyclicTask(accountId, tasksPerHour, tasksCount, status, selectedTimes) {
            await axios.post('http://localhost:8080/api/cyclic-tasks/create-cyclic-task', {
                account_id: accountId,
                tasks_per_hour: tasksPerHour,
                total_task_count: tasksCount,
                status: status,
                selected_times: selectedTimes
            })
        },

        async editCyclicTask(taskId, taskData) {
            await axios.patch(`http://localhost:8080/api/cyclic-tasks/${taskId}`, taskData)
                .then(({ data }) => {
                    // Сервер возвращает обновлённую задачу в ответе
                    const updatedTask = data.data

                    // Находим индекс задачи в массиве
                    const index = this.cyclicTasks.findIndex(task => task.id === taskId)

                    // Если задача найдена, обновляем её данные
                    if (index !== -1) this.cyclicTasks[index] = updatedTask

                    showSuccessNotification(data.message)
                })
        },

        async deleteCyclicTask(taskId) {
            await axios.delete(`http://localhost:8080/api/cyclic-tasks/${taskId}`)
                .then(({ data }) => {
                    // Удаляем задачу из списка
                    const index = this.cyclicTasks.findIndex(cyclicTask => cyclicTask.id === taskId)
                    if (index !== -1) this.cyclicTasks.splice(index, 1)

                    this.totalCyclicTasksCount = this.totalCyclicTasksCount > 0 ? this.totalCyclicTasksCount - 1 : 0

                    // Увеличиваем счетчик удаленных задач
                    this.deletedCyclicTasksCount++
                    showSuccessNotification(data.message)
                })
                .catch(error => showErrorNotification(error))
        },

        async deleteAllCyclicTasks() {
            await axios.delete('http://localhost:8080/api/cyclic-tasks/delete-all-cyclic-tasks')
                .then(({ data }) => {
                    this.cyclicTasks = []
                    showSuccessNotification(data.message)
                })
        },

        async pauseCyclicTask(taskId) {
            await axios.patch(`http://localhost:8080/api/cyclic-tasks/pause-cyclic-task/${taskId}`)
                .then(({ data }) => {
                    const taskIndex = this.cyclicTasks.findIndex(task => task.id === taskId)
                    if (taskIndex !== -1) {
                        this.cyclicTasks[taskIndex].status = this.cyclicTasks[taskIndex].status === 'active' ? 'pause' : 'active'
                    }

                    showSuccessNotification(data.message)
                })
        }
    },

    getters: {
        getTaskById: state => id => state.cyclicTasks.find(task => task.id === id)
    }
})
