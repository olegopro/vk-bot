<template>
    <div class="card" style="width: 18rem;">
        <img class="bd-placeholder-img card-img-top" width="100%" height="180" src="https://placeimg.com/286/180/any" alt="">
        <div class="card-body">
            <h5 class="card-title">{{getAccount.username}}</h5>
            <p class="card-text">{{ getAccount.login }}</p>
            <a href="#" class="btn btn-primary">Показать историю</a>
        </div>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex'

    export default {
        computed: {
            ...mapGetters('account', ['getAccount'])
        },

        async mounted() {
            await this.account(this.$route.params.id)
        },

        methods: {
            ...mapActions('account', ['account'])
        }
    }
</script>

<style scoped>

</style>
