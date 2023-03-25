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
        },

        async saveSettings({ commit }, { showFollowers, showFriends, taskTimeout }) {
            const { data } = await axios.post('http://localhost:8080/api/settings/save', null, {
                params: {
                    show_followers: showFollowers,
                    show_friends: showFriends,
                    task_timeout: taskTimeout
                }
            })
            commit('addSettings', { showFollowers, showFriends, taskTimeout })

            return data
        }
    },

    getters: {
        getSettings(state) {
            return state.settings
        }
    }
}
