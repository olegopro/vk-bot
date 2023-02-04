<template>
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h1 class="h2">Настройки</h1>
        </div>
        <div class="col">
            <button type="submit" form="save-settings" class="btn btn-success btn-action float-end">Сохранить</button>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <form @submit.prevent="save" class="settings" id="save-settings">
                <div class="row align-items-center justify-content-between">
                    <div class="col-6">
                        <div class="form-check form-switch mb-1 d-flex align-items-center">
                            <input id="showFriends"
                                   :checked="getSettings.show_friends === 1"
                                   class="form-check-input"
                                   role="switch"
                                   type="checkbox"
                                   v-model="showFriends"
                            >
                            <label class="form-check-label" for="showFriends">Показывать друзей</label>
                        </div>
                        <div class="form-check form-switch d-flex align-items-center">
                            <input id="showFollowers"
                                   :checked="getSettings.show_followers === 1"
                                   class="form-check-input"
                                   role="switch"
                                   type="checkbox"
                                   v-model="showFollowers"
                            >
                            <label class="form-check-label" for="showFollowers">Показывать подписчиков</label>
                        </div>
                    </div>
                    <div class="col-6 d-flex justify-content-end">
                        <!--<div class="input-group flex-nowrap">-->
                        <!--    <span class="input-group-text">Задержка между задачами</span>-->
                        <!--    <input type="text" class="form-control " v-model="taskTimeout">-->
                        <!--</div>-->

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

<script>
    import { mapActions, mapGetters } from 'vuex'

    export default {
        data() {
            return {
                showFriends: null,
                showFollowers: null,
                taskTimeout: null
            }
        },

        computed: {
            ...mapGetters('settings', ['getSettings'])
        },

        async mounted() {
            await this.settings()
            this.showFriends = await this.getSettings.show_friends === 1
            this.showFollowers = await this.getSettings.show_followers === 1
            this.taskTimeout = await this.getSettings.task_timeout
        },

        methods: {
            ...mapActions('settings', ['settings', 'saveSettings']),

            save() {
                this.saveSettings({
                    showFollowers: this.showFollowers === true ? 1 : 0,
                    showFriends: this.showFriends === true ? 1 : 0,
                    taskTimeout: this.taskTimeout
                })
            }
        }
    }
</script>

<style scoped lang="scss">
    .settings {
        box-shadow: 0 1px 27px 0 rgba(34, 60, 80, 0.2);
        border-radius: 0.675rem;
        padding: 2rem;

        input {
            &:focus{
                box-shadow: none;
            }

            &.form-check-input {
                margin-top: 0;
                height: 24px;
                width: 48px;
            }

            &.form-control{
                max-width: 48px;
            }
        }

        label {
            height: auto;
            margin-bottom: 2px;
            padding-left: 0.8rem;
            font-size: 19px;
        }

        .input-group{
            width: fit-content;
        }
    }
</style>
