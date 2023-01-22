<template>
    <div class="album py-5 bg-light">
        <div class="container">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
                <div class="col" v-for="follower in getAccountFollowers" :key="follower.id">
                    <div class="card shadow-sm">
                        <img class="bd-placeholder-img card-img-top"
                             width="100%"
                             height="200"
                             alt=""
                             :src="follower.photo_200"
                        />
                        <div class="card-body">
                            <p class="card-text">{{ follower.first_name }}</p>
                            <p class="card-text">{{ follower.last_name }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary">View</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary">Edit</button>
                                </div>
                                <small class="text-muted">9 mins</small>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex'

    export default {
        data() {
            return {
                followers: []
            }
        },

        mounted() {
            this.accountFollowers(this.$route.params.id)
        },

        computed: {
            ...mapGetters('account', ['getAccountFollowers'])
        },

        methods: {
            ...mapActions('account', ['accountFollowers'])
        }
    }
</script>

<style scoped lang="scss">

</style>
