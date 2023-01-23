<template>
    <div class="row mt-5">
        <h1>Подписчики</h1>
        <div class="col-2" v-for="follower in getAccountFollowers.items" :key="follower.id">
            <div class="card">
                <img class="bd-placeholder-img card-img-top"
                     width="100%"
                     height="200"
                     alt=""
                     :src="follower.photo_200"
                />
                <div class="card-body">
                    <p class="card-text">{{ follower.first_name }} {{ follower.last_name }}</p>
                    <!--<div class="d-flex justify-content-between align-items-center">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-secondary">View</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Edit</button>
                        </div>
                        <small class="text-muted">9 mins</small>
                    </div>-->
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
    .card {
        border: none;
        box-shadow: 0 1px 27px 0 rgba(34, 60, 80, 0.2);

        .bd-placeholder-img {
            object-fit: cover;
        }

        .card-body {
            .card-text {
                overflow: hidden;
                white-space: nowrap;
                text-overflow: ellipsis;
            }
        }
    }
</style>
