<template>

    <div class="row">
        <div class="col">
            <h1>Настройки</h1>
        </div>
    </div>

    <div class="row settings align-items-center">
        <div class="col-6">
            <div class="form-check form-switch">
                <input id="showFriends"
                       :checked="getSettings.show_friends === 1"
                       class="form-check-input"
                       role="switch"
                       type="checkbox"
                >
                <label class="form-check-label" for="showFriends">Показывать друзей</label>
            </div>
            <div class="form-check form-switch">
                <input id="showFollowers"
                       :checked="getSettings.show_followers === 1"
                       class="form-check-input"
                       role="switch"
                       type="checkbox"
                >
                <label class="form-check-label" for="showFollowers">Показывать подписчиков</label>
            </div>
        </div>
        <div class="col-6">
            <div class="input-group flex-nowrap">
                <span class="input-group-text">Задержка между задачами</span>
                <input type="text" class="form-control" :value="getSettings.task_timeout">
            </div>
        </div>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex'

    export default {
        computed: {
            ...mapGetters('settings', ['getSettings'])
        },

        mounted() {
            this.settings()
        },

        methods: {
            ...mapActions('settings', ['settings'])
        }
    }
</script>

<style scoped lang="scss">
    .settings {
        box-shadow: 0 1px 27px 0 rgba(34, 60, 80, 0.2);
        border-radius: 0.675rem;
        padding: 2rem;
    }
</style>
