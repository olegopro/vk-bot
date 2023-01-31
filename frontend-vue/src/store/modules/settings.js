import axios from 'axios'

export default {
    namespaced: true,

    state() {
        return {
            settings: {}
        }
    },

    mutations: {
        addSettings(state, settings) {
            state.settings = settings
        }
    },

    actions: {
        async settings({ commit }) {
            const { data } = await axios.post('http://localhost:8080/api/settings')
            commit('addSettings', data[0])
        }
    },

    getters: {
        getSettings(state) {
            return state.settings
        }
    }
}
