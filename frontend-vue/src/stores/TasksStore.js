import { defineStore } from 'pinia'
import axios from 'axios'
import { showSuccessNotification } from '../helpers/notyfHelper'

export const useTasksStore = defineStore('tasks', {
	state: () => ({
		tasks: []
	}),

	actions: {
		async fetchTasks(status = null) {
			const url = status ? `http://localhost:8080/api/tasks/${status}` : 'http://localhost:8080/api/tasks'
			await axios.get(url).then(({ data }) => {
				this.tasks = data.data
				showSuccessNotification(data.message)
			})
		},

		async taskDetails(taskId) {
			const { data } = await axios.post(`http://localhost:8080/api/tasks/task-info/${taskId}`)
			return data.data
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

		async deleteAllTasks(status) {
			await axios.delete(`http://localhost:8080/api/tasks/delete-all-tasks/${status}`)
				.then(({ data }) => {
					this.tasks = []
					showSuccessNotification(data.message)
				})
		}
	}
})
