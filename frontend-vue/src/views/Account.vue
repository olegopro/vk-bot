<template>
    <div class="card" style="width: 18rem;">
        <img class="bd-placeholder-img card-img-top" width="200" height="200" :src="getAccountData.photo_200" alt="">
        <div class="card-body">
            <!--<h5 class="card-title">{{ getAccount.username }}</h5>-->
            <h5 class="card-title">Имя - {{ getAccountData.first_name }}</h5>
            <h5 class="card-title">Фамилия - {{ getAccountData.last_name }}</h5>

            <p class="card-text">Статус - {{ getAccountData.status }}</p>
            <p class="card-text">Друг? -  {{ getAccountData.is_friend }}</p>
            <a href="#" class="btn btn-primary">Показать историю</a>
        </div>
    </div>

    <Followers />

</template>

<script>
    import { mapActions, mapGetters } from 'vuex'
    import Followers from '../components/Account/Followers.vue'

    export default {
        components: { Followers },
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
    img {
        object-fit: cover;
    }
</style>
