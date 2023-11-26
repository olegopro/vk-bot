import { defineStore } from 'pinia'
import axios from 'axios'

export const useTasksStore = defineStore('tasks', {
    state: () => ({
        tasks: []
    }),

    actions: {
        async fetchTasks(status = null) {
            const url = status ? `http://localhost:8080/api/tasks/${status}` : 'http://localhost:8080/api/tasks'
            const { data } = await axios.get(url)
            this.tasks = data
        },

        async accountByTaskId(id) {
            return await axios.post(`http://localhost:8080/api/account/task/${id}`)
        },

         async taskDetails(taskId) {
            const response = await axios.post(`http://localhost:8080/api/tasks/task-info/${taskId}`)
            return response.data
        },

        async deleteLike(taskId) {
            await axios.delete(`http://localhost:8080/api/tasks/delete-like/${taskId}`)
            // После удаления лайка обновляем данные о задаче
            this.taskDetails(taskId)
        },

        async deleteTask(id) {
            console.log('deleteTask -id', id)
            await axios.delete(`http://localhost:8080/api/tasks/delete-task-by-id/${id}`)
            this.tasks = this.tasks.filter(task => task.id !== id)
        },

        async deleteSingleTaskById(id) {
            await axios.delete(`http://localhost:8080/api/tasks/deleteTask/${id}`)
            this.tasks = this.tasks.filter(task => task.id !== id)
        },

        async deleteAllTasks() {
            await axios.delete('http://localhost:8080/api/tasks/delete-all-tasks')
            this.tasks = []
        }
    },

    getters: {
        getTasks: state => state.tasks
    }
})
