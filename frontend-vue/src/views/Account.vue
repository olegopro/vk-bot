<template>
    <div class="row account mx-0">
        <img class="col-3 ps-0" width="200" height="200" :src="getAccountData.photo_200" alt="">
        <div class="col-4 p-3">
            <h2>{{ getAccountData.first_name }} {{ getAccountData.last_name }}</h2>
            <h3>{{ getAccountData.screen_name }}</h3>

            <p>Статус - {{ getAccountData.status }}</p>
            <p>Последняя активность - {{ getAccountData.last_seen?.time }}</p>
        </div>
        <div class="col-4 p-3">
            <h3>Друзья - {{ getAccountFriends.count }}</h3>
            <h3>Подписчики - {{ getAccountData.followers_count }}</h3>
            <h3>Город - {{ getAccountData.city?.title }}</h3>

        </div>
        <div class="col-1 p-3">
            <h3>Онлайн - {{ getAccountData.online }}</h3>
        </div>
    </div>

    <!--<Followers />-->
    <Followers />

</template>

<script>
    import { mapActions, mapGetters } from 'vuex'
    import Followers from '../components/Account/Followers.vue'

    export default {
        components: { Followers },

        data() {
            return {
                userID: null
            }
        },

        computed: {
            ...mapGetters('account', ['getAccount', 'getAccountData', 'getAccountFriends'])
        },

        created() {
            this.userID = this.$route.params.id
        },

        async mounted() {
            await this.account(this.userID)
            await this.accountData(this.userID)
            await this.accountFriends(this.userID)
        },

        methods: {
            ...mapActions('account', ['account', 'accountData', 'accountFriends'])
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
