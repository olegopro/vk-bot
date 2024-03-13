import { defineStore } from 'pinia'
import axios from 'axios'
import { showErrorNotification, showSuccessNotification } from '../helpers/notyfHelper'

export const useTasksStore = defineStore('tasks', {
    state: () => ({
        tasks: [],
        totalTasksCount: null,
        totalTasksQueued: null,
        totalTasksFailed: null,
        totalTasksDone: null,
        taskCountByStatus: null,
        isLoading: false
    }),

    actions: {
        async fetchTasks(status = '', accountId = '', page = 1) {
            this.isLoading = true
            /*
                Формирование базового URL для запроса.
                Если параметр status задан (не пустая строка), то он добавляется к URL.
                Если параметр accountId задан, то он также добавляется к URL.
                Например, если status = 'failed' и accountId = '123', URL станет 'http://localhost:8080/api/tasks/failed/123'.
                Если оба параметра не заданы, URL останется 'http://localhost:8080/api/tasks'.
            */
            const url = `http://localhost:8080/api/tasks${status ? `/${status}` : ''}${accountId ? `/${accountId}` : ''}?page=${page}`

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

                showSuccessNotification(data.message)
            })
                .finally(() => this.isLoading = false)
        },

        // async taskDetails(taskId) {
        //     const { data } = await axios.post(`http://localhost:8080/api/tasks/task-info/${taskId}`)
        //     return data.data
        // },

        async taskDetails(taskId) {
            await axios.post(`http://localhost:8080/api/tasks/task-info/${taskId}`)
                .then(({ data }) => data.data)
                .catch(error => {
                    throw error
                })
        },

        async getTasksCountByStatus(status = '', accountId = '') {
            const url = `http://localhost:8080/api/tasks/count-by-status${status ? `/${status}` : ''}${accountId ? `/${accountId}` : ''}`

            await axios.get(url)
                .then(({ data }) => {
                    this.taskCountByStatus = data.data
                    showSuccessNotification(data.message)
                })
        },

        async deleteLike(taskId) {
            await axios.delete(`http://localhost:8080/api/tasks/delete-like/${taskId}`)
                .then(({ data }) => {
                    this.taskDetails(taskId)
                    showSuccessNotification(data.message)
                })
        },

        async deleteTask(id) {
            await axios.delete(`http://localhost:8080/api/tasks/delete-task-by-id/${id}`)
                .then(({ data }) => {
                    this.tasks = this.tasks.filter(task => task.id !== id)
                    showSuccessNotification(data.message)
                })
        },

        async deleteSingleTaskById(id) {
            await axios.delete(`http://localhost:8080/api/tasks/deleteTask/${id}`)
                .then(() => this.tasks = this.tasks.filter(task => task.id !== id))
        },

        async deleteAllTasks(status, accountId) {
            console.log('deleteAllTasks status', status)
            // Проверяем, определены ли параметры status и accountId
            const statusPart = status ? `/${status}` : '/null' // Если status не определен, используем '/null'
            const accountIdPart = accountId ? `/${accountId}` : '' // Если accountId не определен, не добавляем его в URL

            const url = `http://localhost:8080/api/tasks/delete-all-tasks${statusPart}${accountIdPart}`

            await axios.delete(url)
                .then(({ data }) => {
                    this.tasks = []

                    this.fetchTasks(status, accountId)
                    showSuccessNotification(data.message)
                })
        }
    }
})
