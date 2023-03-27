<template>
    <div class="row justify-content-end mt-5">
        <div class="col-3">
            <button type="button" class="btn btn-secondary w-100" @click="refreshNewsfeed" :disabled="newsFeedLoadingStatus">
                Обновить ленту
                <span v-show="newsFeedLoadingStatus" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            </button>
        </div>
    </div>

    <div class="grid-container mt-5 mb-5">
        <div class="item mb-3" v-for="(post, index) in newsfeed" :key="post.source_id">
            <img class="bd-placeholder-img card-img-top"
                 width="100%"
                 height="200"
                 alt=""
                 :src="post.attachments[0].photo.sizes[2].url"
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
    <div class="row justify-content-center mb-5">
        <div class="col-4">
            <button class="btn btn-secondary w-100" @click="loadMore">Загрузить еще</button>
        </div>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex'

    export default {

        data() {
            return {
                userID: null,
                loadingStatus: [],
                newsFeedLoadingStatus: false,
                nextFrom: null
            }
        },

        computed: {
            ...mapGetters('account', ['getAccountNewsFeed', 'getNextFrom']),

            newsfeed() {
                return this.getAccountNewsFeed
            }
        },

        created() {
            this.userID = this.$route.params.id
        },

        mounted() {
            this.accountNewsfeed({
                accountID: this.userID
            })
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
            },

            refreshNewsfeed() {
                this.newsFeedLoadingStatus = true
                this.accountNewsfeed(this.userID)
                    .finally(() => {
                        this.newsFeedLoadingStatus = false
                    })
            },

            loadMore() {
                this.accountNewsfeed({
                    accountID: this.userID,
                    startFrom: this.getNextFrom
                })
            }
        }
    }
</script>

<style scoped lang="scss">
    .grid-container{
        display: grid;
        gap: 10px;
        grid-template-columns: repeat(3, minmax(120px, 1fr));
        grid-template-rows: masonry;
    }

    .item {
        height: fit-content;

        img {
            border-top-left-radius: 6px;
            border-top-right-radius: 6px;
        }

        button {
            width: 100%;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
    }

    .bd-placeholder-img {
        height: auto;
    }
</style>
