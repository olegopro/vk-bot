<template>
    <div class="row mt-5 mb-5">
        <h1>Публикации в ленте (photo)</h1>
        <div class="col-3 mb-3" v-for="post in newsfeed" :key="post.source_id">
            <div class="card">
                <img class="bd-placeholder-img card-img-top"
                     width="100%"
                     height="200"
                     alt=""
                     :src="post.attachments[0].photo.sizes[4].url"
                />
                <button type="button" class="btn btn-danger" @click="addLikeToPost(post.owner_id, post.post_id)">Лайкнуть</button>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex'

    export default {
        data() {
            return {
                userID: null
            }
        },

        computed: {
            ...mapGetters('account', ['getAccountNewsFeed']),

            newsfeed() {
                return this.getAccountNewsFeed
            }
        },

        created() {
            this.userID = this.$route.params.id
        },

        mounted() {
            this.accountNewsfeed(this.userID)
        },

        methods: {
            ...mapActions('account', ['accountNewsfeed', 'addLike']),

            async addLikeToPost(ownerId, itemId) {
                const payload = { accountId: this.userID, ownerId, itemId }
                const data = await this.addLike(payload)

                if (data.response) {
                    console.log('лайк поставлен')
                }
            }
        }
    }
</script>

<style scoped lang="scss">
    .card {
        border: none;
        box-shadow: 0 1px 27px 0 rgba(34, 60, 80, 0.2);

        button {
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }

        .bd-placeholder-img {
            object-fit: cover;
            height: 100%;
            border-top-left-radius: 0.375rem;
            border-top-right-radius: 0.375rem;
        }
    }
</style>
