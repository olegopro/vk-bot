<template>
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h1 class="h2">Настройки</h1>
        </div>
        <div class="col">
            <button type="submit" form="save-settings" class="btn btn-success btn-action float-end">
                Сохранить
                <span v-show="saveSettingStatus" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            </button>
        </div>
    </div>

    <div class="row">

        <div class="col" v-if="loadingStatus">
            <form @submit.prevent="save" class="settings" id="save-settings">
                <div class="row align-items-center justify-content-between">
                    <div class="col-6">
                        <div class="form-check form-switch mb-1 d-flex align-items-center">
                            <input id="showFriends"
                                   :checked="showFriends"
                                   class="form-check-input"
                                   role="switch"
                                   type="checkbox"
                                   v-model="showFriends"
                            >
                            <label class="form-check-label" for="showFriends">Показывать друзей</label>
                        </div>
                        <div class="form-check form-switch d-flex align-items-center">
                            <input id="showFollowers"
                                   :checked="showFollowers"
                                   class="form-check-input"
                                   role="switch"
                                   type="checkbox"
                                   v-model="showFollowers"
                            >
                            <label class="form-check-label" for="showFollowers">Показывать подписчиков</label>
                        </div>
                    </div>
                    <div class="col-6 d-flex justify-content-end">
                        <div class="input-group">
                            <span class="input-group-text">Задержка между задачами</span>
                            <input type="text" class="form-control" v-model="taskTimeout">
                            <span class="input-group-text">сек.</span>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

</template>

<script setup>
    import { ref, onMounted } from 'vue'
    import { useSettingsStore } from '@/stores/SettingsStore'

    const settingsStore = useSettingsStore()

    const showFriends = ref(null)
    const showFollowers = ref(null)
    const taskTimeout = ref(null)
    const loadingStatus = ref(false)
    const saveSettingStatus = ref(false)

    onMounted(async () => {
        await settingsStore.fetchSettings()
        loadingStatus.value = true

        showFriends.value = settingsStore.getSettings.show_friends === 1
        showFollowers.value = settingsStore.getSettings.show_followers === 1
        taskTimeout.value = settingsStore.getSettings.task_timeout
    })

    const save = async () => {
        saveSettingStatus.value = true

        await settingsStore.saveSettings({
            showFollowers: showFollowers.value === true ? 1 : 0,
            showFriends: showFriends.value === true ? 1 : 0,
            taskTimeout: taskTimeout.value
        })

        saveSettingStatus.value = false
    }
</script>

<!--<script>
    import { mapActions, mapGetters } from 'vuex'

    export default {
        data() {
            return {
                showFriends: null,
                showFollowers: null,
                taskTimeout: null,
                loadingStatus: false,
                saveSettingStatus: false
            }
        },

        computed: {
            ...mapGetters('settings', ['getSettings'])
        },

        async mounted() {
            await this.settings()
                .then(() => (this.loadingStatus = true))

            this.showFriends = this.getSettings.show_friends === 1
            this.showFollowers = this.getSettings.show_followers === 1
            this.taskTimeout = this.getSettings.task_timeout
        },

        methods: {
            ...mapActions('settings', ['settings', 'saveSettings']),

            async save() {
                this.saveSettingStatus = true

                await this.saveSettings({
                    showFollowers: this.showFollowers === true ? 1 : 0,
                    showFriends: this.showFriends === true ? 1 : 0,
                    taskTimeout: this.taskTimeout
                })
                    .finally(() => (this.saveSettingStatus = false))
            }
        }
    }
</script>-->

<style scoped lang="scss">
    .settings {
        box-shadow: 0 1px 27px 0 rgba(34, 60, 80, 0.2);
        border-radius: 0.675rem;
        padding: 2rem;

        input {
            &:focus {
                box-shadow: none;
            }

            &.form-check-input {
                margin-top: 0;
                height: 24px;
                width: 48px;
            }

            &.form-control {
                max-width: 48px;
            }
        }

        label {
            height: auto;
            margin-bottom: 2px;
            padding-left: 0.8rem;
            font-size: 19px;
        }

        .input-group {
            width: fit-content;
        }
    }
</style>
