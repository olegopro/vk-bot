<template>
    <div class="card" style="width: 18rem;">
        <svg class="bd-placeholder-img card-img-top" width="100%" height="180" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: Image cap" preserveAspectRatio="xMidYMid slice" focusable="false">
            <title>Placeholder</title>
            <rect width="100%" height="100%" fill="#868e96"></rect>
            <text x="50%" y="50%" fill="#dee2e6" dy=".3em">Image cap</text>
        </svg>
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
            ...mapActions('account', ['account']),

            getAccount(id) {
                this.request = this.account(id)
            }
        }
    }
</script>

<style scoped>

</style>
