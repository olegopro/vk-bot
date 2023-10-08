<template>
    <div class="row justify-content-end mt-5 mb-3">
        <div class="col-3 d-flex justify-content-end change-grid-columns">
            <i @click="changeColumnClass('col-6')" :class="[currentColumnClass === 'col-6' ? 'bi-2-square-fill pe-none' : 'bi-2-square', 'bi', 'me-2']" style="font-size: 2rem;" />
            <i @click="changeColumnClass('col-4')" :class="[currentColumnClass === 'col-4' ? 'bi-3-square-fill pe-none' : 'bi-3-square', 'bi', 'me-2']" style="font-size: 2rem;" />
            <i @click="changeColumnClass('col-3')" :class="[currentColumnClass === 'col-3' ? 'bi-4-square-fill pe-none' : 'bi-4-square', 'bi']" style="font-size: 2rem;" />
        </div>
    </div>

    <div v-masonry item-selector=".item" v-if="showNewsfeed" transition-duration="0s" class="row">
        <div v-masonry-tile
             :class="[currentColumnClass, 'item', 'mb-4', 'placeholder-glow']"
             v-for="(post, index) in newsfeed"
             :key="index"
        >
            <button class="account-info-btn mr-1"
                    :class="{ 'opacity-100': showDetailedInfoButton === index || likedPostIndex === index }"
                    data-bs-target="#accountDetails"
                    data-bs-toggle="modal"
                    type="button"
                    @click="ownerInfo(post.source_id, index)"
                    @mouseover="ownerInfo(post.source_id, index); showDetailedInfoBtn(index)"
                    @mouseleave="hideDetailedInfo"
            >
                <i class="bi bi-info-circle" v-if="!post.source_id"></i> <!-- стандартная иконка информации -->
                <i class="bi bi-person-fill" v-if="post.source_id > 0"></i> <!-- иконка человека -->
                <i class="bi bi-people-fill" v-if="post.source_id < 0"></i> <!-- иконка группы -->
            </button>

            <div class="placeholder-wrapper" v-if="loadingStatus[index]">
                <transition name="fade">
                    <span class="placeholder bg-danger" />
                </transition>
            </div>

            <button class="like-button">
                <i :class="post.likes.user_likes === 1 ? 'bi bi-heart-fill text-danger' : ''"></i>
            </button>

            <div class="content-wrapper"
                 :class="{'radial-red-background': post.likes.user_likes !== 1 }"
                 @mouseover="showDetailedInfoBtn(index)"
                 @mouseleave="hideDetailedInfoBtn"
            >
                <img class="card-img-top"
                     :style="post.likes.user_likes !== 1
                          ? { cursor: 'pointer'}
                          : {}"
                     alt=""
                     :src="getAdjustedQualityImageUrl(post.attachments[0].photo.sizes)"
                     @click="post.likes.user_likes !== 1 && addLikeToPost(post.owner_id, post.post_id, index)"
                />
                <div class="detailed-info" :class="{ 'opacity-100': showDetailedInfo === index }">
                    <h3 class="mb-2" v-if="ownerDataById?.type || ownerDataById?.first_name">
                        <b>{{ ownerDataById?.type ? 'Группа' : 'Аккаунт' }}</b>
                    </h3>

                    <p class="mb-1" v-if="ownerDataById?.country?.title">
                        <b>Страна:</b> {{ ownerDataById?.country?.title }}
                    </p>

                    <p class="mb-1" v-if="ownerDataById?.city?.title">
                        <b>Город:</b> {{ ownerDataById?.city?.title }}
                    </p>

                    <p class="mb-1" v-if="ownerDataById?.friends_count">
                        <b>Друзья:</b> {{ ownerDataById?.friends_count }}
                    </p>

                    <p class="mb-1" v-if="ownerDataById?.followers_count">
                        <b>Подписчики:</b> {{ ownerDataById?.followers_count }}
                    </p>

                    <p class="mb-1" v-if="ownerDataById?.members_count">
                        <b>Подписчики:</b> {{ ownerDataById?.members_count }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center mt-2 mb-4" id="loader">
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
                hoveredOwnerId: null,
                currentColumnClass: 'col-4',
                showNewsfeed: true,
                showDetailedInfo: null,
                showDetailedInfoButton: null,
                likedPostIndex: null
            }
        },

        computed: {
            ...mapGetters('account', ['getAccountNewsFeed', 'getNextFrom', 'getOwnerDataById']),

            infoIconClass() {
                console.log('ownerDataById', this.ownerDataById)
                if (!this.ownerDataById) return 'bi bi-info-circle' // стандартная иконка информации
                return this.ownerDataById.type ? 'bi bi-group-icon' : 'bi bi-person-icon' // замените 'bi-group-icon' и 'bi-person-icon' на реальные классы иконок группы и человека
            },

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
                this.isLoadingFeed = true

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
                this.likedPostIndex = index
                const payload = { accountId: this.userID, ownerId, itemId }
                this.loadingStatus[index] = true

                await this.addLike(payload)
                    .then(() => {
                        showSuccessNotification('Лайк успешно поставлен')
                        // Обновление значения post.likes.user_likes
                        this.newsfeed[index].likes.user_likes = 1
                    })
                    .catch(({ response }) => showErrorNotification(response.data.message))
                    .finally(() => {
                        this.loadingStatus[index] = false
                    })
            },

            refreshNewsfeed() {
                this.newsFeedLoadingStatus = true
                this.addNextFrom = null
                this.accountNewsfeed({ accountID: this.userID })
                    .finally(() => {
                        this.newsFeedLoadingStatus = false
                    })
            },

            async changeColumnClass(newClass) {
                this.showNewsfeed = false
                this.currentColumnClass = newClass
                await this.accountNewsfeed({ accountID: this.userID })
                    .then(() => (this.showNewsfeed = true))
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

            async ownerInfo(accountId, index) {
                this.showDetailedInfo = index
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
            },

            hideDetailedInfo() {
                this.showDetailedInfo = false
            },

            showDetailedInfoBtn(index) {
                this.showDetailedInfoButton = index
            },

            hideDetailedInfoBtn() {
                this.showDetailedInfoButton = null
            },

            getAdjustedQualityImageUrl(sizes) {
                let requiredType

                switch (this.currentColumnClass) {
                    case 'col-6':
                        requiredType = 'w' // оригинал
                        break
                    case 'col-4':
                        requiredType = 'z' // выше среднего
                        break
                    case 'col-3':
                        requiredType = 'x' // ниже среднего
                        break
                    default:
                        requiredType = 'm' // низкое качество (просто на случай, если не подойдет ни одно из условий)
                }

                const sizeObj = sizes.find(size => size.type === requiredType)
                return sizeObj ? sizeObj.url : ''
            }
        }
    }
</script>

<style scoped lang="scss">
    .change-grid-columns {
        i {
            cursor: pointer;
        }
    }

    .item {
        &:hover {
            button.account-info-btn {
                transition: all 0.060s ease-in;
            }

        }

        button {
            width: 100%;
            border-top-left-radius: 0;
            border-top-right-radius: 0;

            &.account-info-btn {
                all: unset;
                cursor: pointer;
                position: absolute;
                top: .5rem;
                right: calc(var(--bs-gutter-x) - 3px);
                z-index: 1;
                opacity: 0;
                transition: opacity 0.2s ease-out;

                i {
                    color: white;
                    font-size: 18px;
                    background: rgba(0, 0, 0, 0.29); /* полупрозрачный черный фон */
                    backdrop-filter: blur(5px); /* размытие заднего фона */
                    border-radius: 6px;
                    padding: 4px 10px;
                }
            }

            &.like-button {
                position: absolute;
                background: none;
                top: 3px;
                left: calc(var(--bs-gutter-x) - 3px);
                width: fit-content;
                border: none;
                z-index: 2;

                i {
                    font-size: 28px;
                }
            }
        }

        .placeholder-wrapper {
            .placeholder {
                position: absolute;
                width: calc(100% - var(--bs-gutter-x));
                height: 100%;
                z-index: 1;
                border-radius: 6px;
                opacity: 0;
            }

            .fade-enter-active, .fade-leave-active {
                transition: opacity .5s;
            }

            .fade-enter-from, .fade-leave-active {
                opacity: 0;
            }

            .fade-enter-to, .fade-leave-from {
                opacity: 0.3;
            }
        }

        .content-wrapper {
            position: relative;

            &.radial-red-background {
                &::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    box-shadow: 0 4px 18px 6px rgba(0, 4, 255, 0.24);
                    //background: radial-gradient(circle at center, transparent, rgba(52, 0, 0, 0.16) 100%);
                    pointer-events: none;
                    opacity: 0;
                    transition: all 0.2s ease-out;
                    border-radius: 6px;
                }

                &:hover {
                    &::before {
                        transition: all 0.060s ease-in;
                        opacity: 1;
                    }
                }
            }

            img {
                border-radius: 6px;
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
        //margin-top: -150vh;
        transition: 0.3s;

        &:before {
            content: '';
            display: block;
            width: 100%;
        }

        .feed-spinner {
            //position: fixed;
            bottom: 1.10rem;
            margin-left: auto;
            margin-right: auto;
            //right: 50%;
            //transform: translateX(-50%);
            color: rgba(0, 0, 0, 0.06);
        }

        .fade-enter-active, .fade-leave-active {
            transition: opacity .5s;
        }

        .fade-enter-from, .fade-leave-active {
            opacity: 0;
        }

        .fade-enter-to, .fade-leave-from {
            opacity: 1;
        }
    }
</style>
