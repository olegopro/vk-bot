import { defineStore } from 'pinia'
import axios from 'axios'
import { showSuccessNotification } from '../helpers/notyfHelper'

export const useCyclicTasksStore = defineStore('cyclicTasks', {
    state: () => ({
        cyclicTasks: [],
        isLoading: false
    }),

    actions: {
        async fetchCyclicTasks(page = 1) {
            this.isLoading = true
            await axios.get(`http://localhost:8080/api/cyclic-tasks?page=${page}`)
                .then(({ data }) => {
                    if (page === 1) {
                        this.cyclicTasks = data.data.data
                    } else {
                        console.log('data.data.cyclicTasks', data.data)
                        this.cyclicTasks = [...this.cyclicTasks, ...data.data.data]
                    }
                    showSuccessNotification(data.message)
                })
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
                    this.cyclicTasks = this.cyclicTasks.filter(task => task.id !== taskId)
                    showSuccessNotification(data.message)
                })
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
