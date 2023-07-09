import axios from 'axios'

export default {
    namespaced: true,

    state() {
        return {
            tasks: []
        }
    },

    mutations: {
        addTasks(state, tasks) {
            state.tasks = tasks
        },

        deleteTask(state, id) {
            // Вариант #1
            // state.tasks = state.tasks.filter(task => task.id !== id)

            // Вариант #2
            state.tasks.splice(state.tasks.findIndex(key => key.id === id), 1)
        },

        deleteAllTasks(state) {
            state.tasks = []
        }
    },

    actions: {
        async tasks({ commit }) {
            const { data } = await axios.post('http://localhost:8080/api/tasks')
            commit('addTasks', data)
        },

        async accountByTaskId(_, id) {
            return await axios.post(`http://localhost:8080/api/account/task/${id}`)
        },

        async deleteTask({ commit }, id) {
            await axios.delete(`http://localhost:8080/api/tasks/${id}`)
            commit('deleteTask', id)
        },

        async deleteAllTasks({ commit }) {
            await axios.delete('http://localhost:8080/api/tasks/delete-all-tasks')
                .then(() => commit('deleteAllTasks'))
        }
    },

    getters: {
        getTasks(state) {
            return state.tasks
        }
    }
}
