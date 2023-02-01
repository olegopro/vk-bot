<template>
    <div class="row account mx-0">
        <img class="col-3 ps-0" width="200" height="200" :src="getAccountData.photo_200" alt="">
        <div class="col-4 p-3">
            <h2>{{ getAccountData.first_name }} {{ getAccountData.last_name }}</h2>
            <h3>{{ getAccountData.screen_name }}</h3>

            <p>Статус - {{ getAccountData.status }}</p>
            <p>Последняя активность - {{ date(getAccountData.last_seen?.time) }}</p>
        </div>
        <div class="col-4 p-3 d-flex flex-column justify-content-around">
            <h3>Друзья - {{ getAccountFriendsCount.count }}</h3>
            <h3>Подписчики - {{ getAccountData.followers_count }}</h3>
            <h3>Город - {{ getAccountData.city?.title }}</h3>
        </div>
        <div class="col-1 p-3">
            <OnlineStatus :type="getAccountData.online === 0 ? 'offline' : 'online'" />
        </div>
    </div>

    <Followers v-if="getSettings.show_followers === 1" />
    <Friends v-if="getSettings.show_friends === 1"/>
    <Newsfeed />

</template>

<script>
    import { mapActions, mapGetters } from 'vuex'
    import Followers from '../components/Account/Followers.vue'
    import Friends from '../components/Account/Friends.vue'
    import OnlineStatus from '../components/Account/OnlineStatus.vue'
    import Newsfeed from '../components/Account/Newsfeed.vue'

    export default {
        components: { OnlineStatus, Followers, Friends, Newsfeed },

        data() {
            return {
                userID: null
            }
        },

        computed: {
            ...mapGetters('account', ['getAccount', 'getAccountData', 'getAccountFriendsCount']),
            ...mapGetters('settings', ['getSettings'])
        },

        created() {
            this.userID = this.$route.params.id
        },

        async mounted() {
            await this.settings()
            await this.account(this.userID)
            await this.accountData(this.userID)
            await this.accountFriendsCount(this.userID)
        },

        methods: {
            ...mapActions('account', ['account', 'accountData', 'accountFriendsCount']),
            ...mapActions('settings', ['settings']),

            date(timestamp) {
                return new Date(timestamp).toLocaleTimeString('ru-RU')
            }
        }
    }
</script>

<style scoped lang="scss">
    .account {
        box-shadow: 0 1px 27px 0 rgba(34, 60, 80, 0.2);
        border-radius: 0.375rem;

        img {
            border-top-left-radius: 0.375rem;;
            border-bottom-left-radius: 0.375rem;;
            width: 200px;
            height: 200px;
        }
    }
</style>
