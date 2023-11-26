import { defineStore } from 'pinia'
import axios from '@/services/axios'

export const useSettingsStore = defineStore('settings', {
    state: () => ({
        settings: {}
    }),

    actions: {
        async fetchSettings() {
            const { data } = await axios.post('/api/settings')
            this.settings = data[0]
        },
        async saveSettings({ showFollowers, showFriends, taskTimeout }) {
            await axios.post('/api/settings/save', null, {
                params: {
                    show_followers: showFollowers,
                    show_friends: showFriends,
                    task_timeout: taskTimeout
                }
            })
            this.settings = { showFollowers, showFriends, taskTimeout }
        }
    },

    getters: {
        getSettings: state => () => state.settings
    }
})
