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

		async deleteCyclicTask(taskId) {
			await axios.delete(`http://localhost:8080/api/cyclic-tasks/${taskId}`)
				.then(({ data }) => {
					this.cyclicTasks = this.cyclicTasks.filter(task => task.id !== taskId)
					showSuccessNotification(data.message)
				})
		}
	}
})
