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

        async taskDetails(taskId) {
            const response = await axios.post(`http://localhost:8080/api/tasks/task-info/${taskId}`)
            return response.data
        },

        async deleteLike(taskId) {
            await axios.delete(`http://localhost:8080/api/tasks/delete-like/${taskId}`)
                .then(() => this.taskDetails(taskId))
        },

        async deleteTask(id) {
            await axios.delete(`http://localhost:8080/api/tasks/delete-task-by-id/${id}`)
                .then(() => this.tasks = this.tasks.filter(task => task.id !== id))
        },

        async deleteSingleTaskById(id) {
            await axios.delete(`http://localhost:8080/api/tasks/deleteTask/${id}`)
                .then(() => this.tasks = this.tasks.filter(task => task.id !== id))
        },

        async deleteAllTasks() {
            await axios.delete('http://localhost:8080/api/tasks/delete-all-tasks')
                .then(() => this.tasks = [])
        }
    }
})
