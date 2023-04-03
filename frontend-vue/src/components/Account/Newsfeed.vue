<template>
    <div class="row justify-content-end mt-5">
        <div class="col-3">
            <button type="button" class="btn btn-secondary w-100" @click="refreshNewsfeed" :disabled="newsFeedLoadingStatus">
                Обновить ленту
                <span v-show="newsFeedLoadingStatus" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            </button>
        </div>
    </div>

    <div v-masonry item-selector=".item" transition-duration="0s" class="row mt-3">
        <div v-masonry-tile
             class="col-4 item mb-4"
             v-for="(post, index) in newsfeed"
             :key="index"
        >

            <img class="card-img-top"
                 alt=""
                 :src="post.attachments[0].photo.sizes[2].url"
                 @dblclick="addLikeToPost(post.owner_id, post.post_id, index)"
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

    <div class="row justify-content-center mb-3" id="loader" v-if="isLoadingFeed">
        <div class="feed-spinner spinner-border text-secondary" role="status">
            <span class="visually-hidden">Загрузка...</span>
        </div>
    </div>

</template>

<script>
    import { mapActions, mapGetters, mapMutations } from 'vuex'

    export default {
        data() {
            return {
                userID: null,
                loadingStatus: [],
                newsFeedLoadingStatus: false,
                nextFrom: null,
                isLoadingFeed: false
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
            if (!this.getNextFrom) {
                this.accountNewsfeed({
                    accountID: this.userID,
                    startFrom: this.getNextFrom
                })
                    .then(() => (this.isLoadingFeed = true))
                    .then(() => {
                        const loadingObserver = new IntersectionObserver(entries => {
                            /* для каждого наблюдаемого элемента */
                            entries.forEach(entry => {
                                if (entry.isIntersecting) {
                                    this.loadMore()
                                }
                            })
                        }, {
                            threshold: 0
                        })
                        /* указываем, что необходимо наблюдать за лоадером */
                        loadingObserver.observe(document.getElementById('loader'))
                    })
                this.loadingStatus = new Array(this.newsfeed.length).fill(false)
            }
        },

        methods: {
            ...mapActions('account', ['accountNewsfeed', 'addLike']),
            ...mapMutations('account', ['addNextFrom']),

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
                this.addNextFrom = null
                this.accountNewsfeed({ accountID: this.userID })
                    .finally(() => {
                        this.newsFeedLoadingStatus = false
                    })
            },

            async loadMore() {
                await this.accountNewsfeed({
                    accountID: this.userID,
                    startFrom: this.getNextFrom
                })
            }
        }
    }
</script>

<style scoped lang="scss">
    .item {
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

    #loader {
        margin-top: -100vh;

        &:before {
            content: "";
            display: block;
            width: 100%;
            height: 100vh;
            //background: #00000045;
        }
    }
</style>
