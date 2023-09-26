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

            <button class="account-info-btn"
                    data-bs-target="#accountDetails"
                    data-bs-toggle="modal"
                    type="button"
                    @click="ownerInfo(post.owner_id)"
            >

                <i class="bi bi-info-circle" @mouseover="ownerInfo(post.owner_id)" />
            </button>

            <div class="content-wrapper">
                <img class="card-img-top"
                     alt=""
                     :src="post.attachments[0].photo.sizes[3].url"
                     @dblclick="post.likes.user_likes !== 1 && addLikeToPost(post.owner_id, post.post_id, index)"
                />
                <div class="detailed-info">
                    <h3 class="mb-2" v-if="ownerDataById?.type || ownerDataById?.first_name"><b>{{ ownerDataById?.type ? 'Группа' : 'Аккаунт' }}</b></h3>
                    <p class="mb-1" v-if="ownerDataById?.country?.title"><b>Страна:</b> {{ ownerDataById?.country?.title }}</p>
                    <p class="mb-1" v-if="ownerDataById?.city?.title"><b>Город:</b> {{ ownerDataById?.city?.title }}</p>
                    <p class="mb-1" v-if="ownerDataById?.friends_count"><b>Друзья:</b> {{ ownerDataById?.friends_count }}</p>
                    <p class="mb-1" v-if="ownerDataById?.followers_count"><b>Подписчики:</b> {{ ownerDataById?.followers_count }}</p>
                    <p class="mb-1" v-if="ownerDataById?.members_count"><b>Подписчики:</b> {{ ownerDataById?.members_count }}</p>
                </div>
            </div>

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

    <div class="row justify-content-center" id="loader">
        <transition name="fade">
            <div class="feed-spinner spinner-border" role="status" v-show="isLoadingFeed">
                <span class="visually-hidden">Загрузка...</span>
            </div>
        </transition>

    </div>

    <Teleport to="body">
        <AccountDetails :owner-data="ownerDataById" />
    </Teleport>

</template>

<script>
    import { mapActions, mapGetters, mapMutations } from 'vuex'
    import OwnerDetails from './Modals/OwnerDetails.vue'
    import { showErrorNotification, showSuccessNotification } from '../../helpers/notyfHelper'

    export default {
        components: { AccountDetails: OwnerDetails },
        data() {
            return {
                userID: null,
                loadingStatus: [],
                newsFeedLoadingStatus: false,
                nextFrom: null,
                isLoadingFeed: false,
                ownerDataById: null,
                hoveredOwnerId: null
            }
        },

        computed: {
            ...mapGetters('account', ['getAccountNewsFeed', 'getNextFrom', 'getOwnerDataById']),

            newsfeed() {
                return this.getAccountNewsFeed
            }
        },

        created() {
            this.userID = this.$route.params.id
        },

        mounted() {
            /*
                Этот подход с throttle будет гарантировать, что функция loadMore не вызывается чаще,
                чем каждые 300 миллисекунд, при этом не создавая "очередь" из вызовов и не внося
                дополнительных задержек.
            */
            let lastCallTime = 0
            const throttleTime = 750 // Задержка в миллисекундах

            if (!this.getNextFrom) {
                this.accountNewsfeed({
                    accountID: this.userID,
                    startFrom: this.getNextFrom
                })
                    .then(() => {
                        const vh = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0)
                        const rootMarginPixels = Math.floor(vh * 1.5) // для 150vh

                        const loadingObserver = new IntersectionObserver(entries => {
                            entries.forEach(entry => {
                                if (entry.isIntersecting) {
                                    const currentTime = Date.now()

                                    if (currentTime - lastCallTime >= throttleTime) {
                                        lastCallTime = currentTime
                                        this.loadMore()
                                    }
                                }
                            })
                        }, {
                            threshold: 0,
                            rootMargin: `${rootMarginPixels}px`
                        })

                        loadingObserver.observe(document.getElementById('loader'))
                    })
                    .catch(error => showErrorNotification(error.message))

                this.loadingStatus = new Array(this.newsfeed.length).fill(false)
            }
        },

        methods: {
            ...mapActions('account', ['accountNewsfeed', 'addLike', 'ownerData', 'groupData']),
            ...mapMutations('account', ['addNextFrom']),

            async addLikeToPost(ownerId, itemId, index) {
                this.disableButton(itemId)
                const payload = { accountId: this.userID, ownerId, itemId }
                this.loadingStatus[index] = true

                await this.addLike(payload)
                    .then(() => {
                        const button = this.$refs.buttons.find(item => Number(item.id) === itemId)
                        button.classList.remove('btn-danger')
                        button.classList.add('btn-success')

                        showSuccessNotification('Лайк успешно поставлен')

                        // Обновление значения post.likes.user_likes
                        this.newsfeed[index].likes.user_likes = 1
                    })
                    .finally(() => {
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
                this.isLoadingFeed = true
                await this.accountNewsfeed({
                    accountID: this.userID,
                    startFrom: this.getNextFrom
                })
                    .catch(() => showErrorNotification('Ошибка в loadMore()'))
                    .finally(() => (this.isLoadingFeed = false))
            },

            async ownerInfo(accountId) {
                this.ownerDataById = null

                if (accountId > 0) {
                    await this.ownerData(accountId).then(() => {
                        this.ownerDataById = this.getOwnerDataById(accountId)
                    })
                        .catch(({ response }) => showErrorNotification(response.data.message))
                }

                if (accountId < 0) {
                    await this.groupData(accountId).then(() => {
                        this.ownerDataById = this.getOwnerDataById(accountId)
                    })
                        .catch(({ response }) => showErrorNotification(response.data.message))
                }
            }
        }
    }
</script>

<style scoped lang="scss">
    .item {
        &:hover {
            button.account-info-btn {
                opacity: 1;
                transition: opacity .2s;
            }

        }

        button {
            width: 100%;
            border-top-left-radius: 0;
            border-top-right-radius: 0;

            &.account-info-btn {
                &:hover {
                    + .content-wrapper .detailed-info {
                        opacity: 1;
                    }
                }
            }
        }

        .content-wrapper {
            position: relative;

            img {
                border-top-left-radius: 6px;
                border-top-right-radius: 6px;
            }

            .detailed-info {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                padding: 2rem;
                color: white;
                background: rgba(0, 0, 0, 0.4);
                border-radius: 6px 6px 0 0;
                opacity: 0;
                pointer-events: none; // Чтобы не мешал другим элементам
                transition: opacity .2s ease;
                font-size: 1.3rem;
            }
        }
    }

    #loader {
        margin-top: -150vh;
        transition: 0.3s;

        &:before {
            content: '';
            display: block;
            width: 100%;
        }

        .feed-spinner {
            position: fixed;
            top: 1rem;
            right: 1rem;
            color: rgba(0, 0, 0, 0.06);
        }

        .fade-enter-active {
            transition: opacity .5s;
        }

        .fade-leave-active {
            transition: opacity .5s;
        }

        .fade-enter-from, .fade-leave-active {
            opacity: 0;
        }

        .fade-enter-to, .fade-leave-from {
            opacity: 1;
        }
    }

    button.account-info-btn {
        all: unset;
        cursor: pointer;
        position: absolute;
        top: .5rem;
        right: 5%;
        z-index: 1;
        opacity: 0;
        border-radius: 50%;
        background: white;

        .bi-info-circle {
            font-size: 28px;
            width: 28px;
            height: 28px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            margin: .2rem;

        }
    }
</style>
