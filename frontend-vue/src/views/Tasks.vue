<template>
	<div class="row mb-3 align-items-center">
		<div class="col">
			<h1 class="h2">Список задач</h1>
		</div>
		<div class="col d-flex justify-content-end">

			<select class="form-select me-3 w-auto" @change="filterTasks" v-model="currentStatus">
				<option value="">Все задачи</option>
				<option value="failed">C ошибками</option>
				<option value="pending">В ожидании</option>
				<option value="done">Завершённые</option>
			</select>

			<button class="btn btn-danger btn-action me-3"
					:disabled="tasksStore.tasks.length === 0"
					@click="showDeleteAllTasksModal"
			>
				Очистить список
			</button>

			<button class="btn btn-success btn-action"
					type="button"
					@click="showAddTaskModal"
			>
				Добавить задачу
			</button>

		</div>

	</div>

	<div class="row">
		<div class="col-12">
			<table v-if="tasksStore.tasks.length" class="table table-hover mb-4">
				<thead>
					<tr>
						<th scope="col">#</th>
						<th scope="col">Имя и фамилия</th>
						<th scope="col">Статус</th>
						<th scope="col">Попытки</th>
						<th scope="col">Действия</th>
						<th scope="col">Старт задачи</th>
						<th scope="col">Задача создана</th>
					</tr>
				</thead>

				<tbody>
					<TableThread
						v-for="task in tasksStore.tasks"
						:task="task"
						:key="task.id"
						:showTaskDetailsModal="showTaskDetailsModal"
						:showDeleteTaskModal="showDeleteTaskModal"
						:showAccountDetailsModal="showAccountDetailsModal"
					/>
				</tbody>
			</table>

			<h3 v-else class="text-center">Список задач пустой</h3>

		</div>
	</div>

	<Teleport to="body">
		<AddTask :modalInstance="addTasksModal" />
		<TaskDetails :modalInstance="taskDetailsModal" :taskData="taskDetailsData" />
		<AccountDetails :modalInstance="accountDetailsModal" :accountData="accountDetailsData" />
		<DeleteTask :modalInstance="deleteTaskModal" :taskId="taskId" />
		<DeleteAllTasks :modalInstance="deleteAllTasksModal" :selectedTasksStatus="currentStatus" />
	</Teleport>

</template>

<script setup>
	import { onMounted, ref } from 'vue'
	import { useTasksStore } from '@/stores/TasksStore'
	import { useAccountStore } from '../stores/AccountStore'
	import { useRoute, useRouter } from 'vue-router'
	import { showErrorNotification } from '../helpers/notyfHelper'
	import TableThread from '../components/Tasks/TableThread.vue'
	import AddTask from '../components/Tasks/Modals/AddTask.vue'
	import AccountDetails from '../components/Tasks/Modals/AccountDetails.vue'
	import TaskDetails from '../components/Tasks/Modals/TaskDetails.vue'
	import DeleteTask from '../components/Tasks/Modals/DeleteTask.vue'
	import DeleteAllTasks from '../components/Tasks/Modals/DeleteAllTasks.vue'
	import { Modal } from 'bootstrap'
    import { useAccountsStore } from '../stores/AccountsStore'

	const taskDetailsModal = ref(null)
	const deleteTaskModal = ref(null)
	const deleteAllTasksModal = ref(null)
	const accountDetailsModal = ref(null)
	const addTasksModal = ref(null)

	const tasksStore = useTasksStore()
	const accountsStore = useAccountsStore()
	const accountStore = useAccountStore()
	const route = useRoute()
	const router = useRouter()

	const taskId = ref(0)

	const currentStatus = ref(route.params.status || '')
	const accountDetailsData = ref(null)
	const taskDetailsData = ref(null)

	const filterTasks = (event) => {
		const status = event.target.value
		router.push({ name: 'Tasks', params: { status } })
		tasksStore.fetchTasks(status)
	}

	const showAccountDetailsModal = (accountId, ownerId) => {
		accountDetailsData.value = null
		accountDetailsModal.value.show()

		accountStore.fetchOwnerData(accountId, ownerId)
			.then(() => {
				const ownerData = accountStore.getOwnerDataById(ownerId)
				accountDetailsData.value = { ...ownerData }
			})
			.catch(error => showErrorNotification(error))
	}

	const showTaskDetailsModal = async (newTaskId) => {
		taskDetailsData.value = null // Очищаем предыдущие данные
		taskDetailsModal.value.show() // Показываем модальное окно

		const response = await tasksStore.taskDetails(newTaskId)
		taskDetailsData.value = { ...response, taskId: newTaskId } // Добавляем taskId в response
	}

	const showDeleteTaskModal = (id) => {
		taskId.value = id
		deleteTaskModal.value.show()
	}

	const showDeleteAllTasksModal = () => deleteAllTasksModal.value.show()

	const showAddTaskModal = () => {
        accountsStore.fetchAccounts()
        addTasksModal.value.show()
    }

	onMounted(() => {
		tasksStore.fetchTasks(currentStatus.value)
		deleteTaskModal.value = new Modal(document.getElementById('deleteTask'))
		taskDetailsModal.value = new Modal(document.getElementById('taskDetails'))
		deleteAllTasksModal.value = new Modal(document.getElementById('deleteAllTasks'))
		accountDetailsModal.value = new Modal(document.getElementById('accountDetails'))
		addTasksModal.value = new Modal(document.getElementById('addTask'))
	})
</script>
