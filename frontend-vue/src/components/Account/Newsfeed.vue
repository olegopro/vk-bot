<template>
    <div class="row mt-5 mb-5" v-masonry transition-duration="0s" item-selector=".item">
        <!--<h1>Публикации в ленте</h1>-->
        <div class="col-3 mb-3 item" v-masonry-tile v-for="(post, index) in newsfeed" :key="post.source_id">
            <div class="card">
                <img class="bd-placeholder-img card-img-top"
                     width="100%"
                     height="200"
                     alt=""
                     :src="post.attachments[0].photo.sizes[4].url"
                />
                <button class="btn btn-danger"
                        type="button"
                        :id="post.post_id"
                        :disabled="post.likes.user_likes === 1"
                        @click="addLikeToPost(post.owner_id, post.post_id, index)"
                        ref="buttons"
                >
                    Лайкнуть
                    <span v-show="loadingStatus[index]" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex'

    export default {
        data() {
            return {
                userID: null,
                loadingStatus: []
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
            this.loadingStatus = new Array(this.newsfeed.length).fill(false)
        },

        methods: {
            ...mapActions('account', ['accountNewsfeed', 'addLike']),

            async addLikeToPost(ownerId, itemId, index) {
                this.disableButton(itemId)

                const payload = { accountId: this.userID, ownerId, itemId }
                this.loadingStatus[index] = true

                await this.addLike(payload)
                    .then(() => {
                        const button = this.$refs.buttons.find(item => +item.id === itemId)
                        button.classList.remove('btn-danger')
                        button.classList.add('btn-success')
                    }).finally(() => {
                        this.loadingStatus[index] = false
                    })
            },

            disableButton(sourceId) {
                this.$refs
                    .buttons.find(item => +item.id === sourceId)
                    .setAttribute('disabled', 'true')
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
