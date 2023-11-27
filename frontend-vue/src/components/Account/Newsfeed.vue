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
             v-for="(post, index) in accountStore.accountNewsFeed"
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
                 :style="post.likes.user_likes === 1 ? {'box-shadow': '0 0 0 2px var(--bs-danger)'} : {}"
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
                    <p class="mb-1 fs-4" v-if="ownerDataById?.first_name">
                        <b>{{ ownerDataById?.first_name }} {{ ownerDataById?.last_name }}</b>
                    </p>

                    <p class="mb-1 fs-4" v-if="ownerDataById?.name">
                        <b>{{ ownerDataById?.name }}</b>
                    </p>
                    <p class="mb-1 fs-4" v-if="ownerDataById?.description">
                        <b>Описание:</b> {{ ownerDataById?.description }}
                    </p>

                    <p class="mb-1" v-if="ownerDataById?.last_seen?.time">
                        <b>Онлайн: </b> {{ date(ownerDataById?.last_seen?.time) }}
                    </p>

                    <p class="mb-1" v-if="ownerDataById?.status">
                        <b>Статус:</b> {{ ownerDataById?.status }}
                    </p>

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
            <div class="feed-spinner spinner-border" role="status" v-show="accountStore.isLoadingFeed">
                <span class="visually-hidden">Загрузка...</span>
            </div>
        </transition>
    </div>

    <Teleport to="body">
        <OwnerDetails :owner-data="ownerDataById" />
    </Teleport>

</template>

<script setup>
    import { onMounted, ref } from 'vue'
    import { useAccountStore } from '@/stores/AccountStore'
    import { showErrorNotification, showSuccessNotification } from '../../helpers/notyfHelper'
    import OwnerDetails from './Modals/OwnerDetails.vue'
    import { useRoute } from 'vue-router'

    const accountStore = useAccountStore()
    const route = useRoute()

    const nextFrom = ref(accountStore.nextFrom)
    const ownerDataById = ref(null)
    const userID = ref(null)
    const loadingStatus = ref([])
    const currentColumnClass = ref('col-4')
    const showNewsfeed = ref(true)
    const showDetailedInfo = ref(null)
    const showDetailedInfoButton = ref(null)
    const likedPostIndex = ref(null)

    const addLikeToPost = async (ownerId, itemId, index) => {
        likedPostIndex.value = index
        loadingStatus.value[index] = true

        await accountStore.addLike(userID.value, ownerId, itemId)
            .then(() => {
                showSuccessNotification('Лайк успешно поставлен')
                accountStore.accountNewsFeed[index].likes.user_likes = 1
            })
            .catch(({ response }) => showErrorNotification(response.data.message))
            .finally(() => (loadingStatus.value[index] = false))
    }

    const date = (timestamp) => {
        return new Date(timestamp * 1000).toLocaleTimeString('ru-RU')
    }

    const hideDetailedInfo = () => (showDetailedInfo.value = false)

    const showDetailedInfoBtn = index => (showDetailedInfoButton.value = index)

    const hideDetailedInfoBtn = () => (showDetailedInfoButton.value = null)

    const getAdjustedQualityImageUrl = (sizes) => {
        let requiredType

        switch (currentColumnClass.value) {
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

        let sizeObj = sizes.find(size => size.type === requiredType)

        // Если sizeObj не найден, пытаемся найти размер 'z'
        if (!sizeObj) {
            sizeObj = sizes.find(size => size.type === 'z')
        }

        // Если sizeObj не найден, пытаемся найти размер 'x'
        if (!sizeObj) {
            sizeObj = sizes.find(size => size.type === 'x')
        }

        // Если sizeObj не найден, пытаемся найти размер 'm'
        if (!sizeObj) {
            sizeObj = sizes.find(size => size.type === 'm')
        }

        return sizeObj ? sizeObj.url : ''
    }

    const ownerInfo = async (accountId, index) => {
        showDetailedInfo.value = index
        ownerDataById.value = null

        if (accountId > 0) {
            await accountStore.fetchOwnerData(accountId).then(() => {
                ownerDataById.value = accountStore.getOwnerDataById(accountId)
            })
                .catch(({ response }) => showErrorNotification(response.data.message))
        }

        if (accountId < 0) {
            await accountStore.groupData(accountId).then(() => {
                ownerDataById.value = accountStore.getOwnerDataById(accountId)
            })
                .catch(data => console.log('response', data))
        }
    }

    const changeColumnClass = async (newClass) => {
        accountStore.accountNewsFeed = []
        accountStore.nextFrom = null
        accountStore.previousNextFrom = null

        showNewsfeed.value = false
        currentColumnClass.value = newClass
        await accountStore.fetchAccountNewsFeed(userID.value)
            .then(() => (showNewsfeed.value = true))
    }

    const loadMore = async () => {
        accountStore.isLoadingFeed = true
        await accountStore.fetchAccountNewsFeed(userID.value, accountStore.nextFrom)
            .catch(() => showErrorNotification('Ошибка в loadMore()'))
    }

    /*
        Этот подход с throttle будет гарантировать, что функция loadMore не вызывается чаще,
        чем каждые 300 миллисекунд, при этом не создавая "очередь" из вызовов и не внося
        дополнительных задержек.
    */
    onMounted(() => {
        userID.value = route.params.id

        let lastCallTime = 0
        const throttleTime = 750 // Задержка в миллисекундах

        if (!accountStore.nextFrom) {
            accountStore.isLoadingFeed = true

            accountStore.fetchAccountNewsFeed({
                accountID: userID.value,
                startFrom: nextFrom
            })
                .then(() => {
                    const loadingObserver = new IntersectionObserver(entries => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                const currentTime = Date.now()

                                if (currentTime - lastCallTime >= throttleTime) {
                                    lastCallTime = currentTime
                                    loadMore()
                                }
                            }
                        })
                    }, { threshold: 0 })

                    loadingObserver.observe(document.getElementById('loader'))
                })
                .catch(error => showErrorNotification(error.message))

            loadingStatus.value = new Array(accountStore.accountNewsFeed.length).fill(false)
        }
    })
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
            border-radius: 6px;

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
                padding: 2.4rem 2.6rem 2.6rem;
                color: white;
                background: rgba(0, 0, 0, 0.65);
                border-radius: 6px;
                opacity: 0;
                pointer-events: none; // Чтобы не мешал другим элементам
                transition: opacity .2s ease;
                font-size: 1.3rem;
                //overflow: auto;
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
