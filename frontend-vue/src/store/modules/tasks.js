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
            state.tasks(id, 1)
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
            await axios.delete((`http://localhost:8080/api/tasks/${id}`))
            // commit('deleteTask', id)
        }
    },

    getters: {
        getTasks(state) {
            return state.tasks
        }
    }
}
