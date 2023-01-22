<template>
    <div class="row account">

        <img class="col-3 ps-0" width="200" height="200" :src="getAccountData.photo_200" alt="">
        <div class="col-4 p-3">
            <h2>{{ getAccountData.first_name }} {{ getAccountData.last_name }}</h2>
            <h3>{{ getAccountData.screen_name }}</h3>

            <p>Статус - {{ getAccountData.status }}</p>
            <p>Последняя активность - {{ getAccountData.last_seen?.time }}</p>
        </div>

        <div class="col-4 p-3">
            <h3>Друзья - </h3>
            <h3>Подписчики - {{ getAccountData.followers_count }}</h3>
            <h3>Город - {{ getAccountData.city?.title }}</h3>

        </div>

        <div class="col-1 p-3">
            <h3>Онлайн - {{ getAccountData.online }}</h3>
        </div>

    </div>

    <!--<Followers />-->

</template>

<script>
    import { mapActions, mapGetters } from 'vuex'

    export default {
        computed: {
            ...mapGetters('account', ['getAccount', 'getAccountData'])
        },

        async mounted() {
            await this.account(this.$route.params.id)
            await this.accountData(this.$route.params.id)
        },

        methods: {
            ...mapActions('account', ['account', 'accountData'])
        }
    }
</script>

<style scoped lang="scss">
    .account {
        box-shadow: 0 1px 27px 0 rgba(34, 60, 80, 0.2);
        border-radius: 2rem;

        img {
            border-top-left-radius: 2rem;
            border-bottom-left-radius: 2rem;
            width: 200px;
            height: 200px;
        }
    }
</style>
