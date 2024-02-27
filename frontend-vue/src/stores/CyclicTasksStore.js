import { defineStore } from 'pinia'
import axios from 'axios'
import { showSuccessNotification } from '../helpers/notyfHelper'

export const useCyclicTasksStore = defineStore('cyclicTasks', {
	state: () => ({
		cyclicTasks: []
	}),

	actions: {
		async fetchCyclicTasks() {
			await axios.get('http://localhost:8080/api/cyclic-tasks')
				.then(({ data }) => {
					this.cyclicTasks = data.data
					showSuccessNotification(data.message)
				})
		},

		async createCyclicTask(accountId, tasksPerHour, tasksCount, status) {
			await axios.post('http://localhost:8080/api/cyclic-tasks/create-cyclic-task', {
				account_id: accountId,
				tasks_per_hour: tasksPerHour,
				tasks_count: tasksCount,
				status: status
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
	}
})
