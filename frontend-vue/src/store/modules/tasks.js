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
        }
    },

    actions: {
        async tasks({ commit }) {
            const { data } = await axios.post('http://localhost:8080/api/tasks')
            console.log(data)
            commit('addTasks', data)
        }
    },

    getters: {
        getTasks(state) {
            return state.tasks
        }
    }
}
