<template>
    <div class="row justify-content-end mt-5 mb-3">
        <div class="col-3 d-flex justify-content-end change-grid-columns">
            <i :class="[columnSettings.columnClass === option.class ? option.iconFill : option.icon, 'bi', 'me-2']"
               v-for="option in columnOptions"
               :key="option.class"
               @click="changeColumnClass(option.class)"
            />
        </div>
    </div>

    <div v-masonry item-selector=".item" v-if="showNewsfeed" transition-duration="0s" class="row">
        <div v-masonry-tile
             :class="[currentColumnClass, 'item', 'mb-4', 'placeholder-glow']"
             v-for="(post, index) in accountStore.accountNewsFeed"
             :key="index"
             @mouseleave="hideDetailedInfo"
        >
            <button class="account-info-btn mr-1"
                    :class="{ 'opacity-100': showDetailedInfoButton === index || likedPostIndex === index }"
                    @click="showOwnerDetailsModal(post.source_id, index)"
                    @mouseover="ownerInfo(post.source_id, index); toggleDetailedInfoBtn(index, true)"
            >
                <i :class="iconClasses.info" v-if="!post.source_id" />
                <i :class="iconClasses.person" v-if="post.source_id > 0" />
                <i :class="iconClasses.people" v-if="post.source_id < 0" />
            </button>

            <div class="placeholder-wrapper" v-if="loadingStatus[index]">
                <transition name="fade">
                    <span class="placeholder bg-danger" v-show="loadingStatus[index]" />
                </transition>
            </div>

            <button class="like-button">
                <i :class="post.likes.user_likes === 1 ? 'bi bi-heart-fill text-danger' : ''"></i>
            </button>

            <div class="content-wrapper"
                 :style="post.likes.user_likes === 1 ? {'box-shadow': '0 0 0 2px var(--bs-danger)'} : {}"
                 :class="{'radial-red-background': post.likes.user_likes !== 1 }"
                 @mouseover="toggleDetailedInfoBtn(index, true)"
                 @mouseleave="toggleDetailedInfoBtn(index, false)"
            >
                <img class="card-img-top"
                     :style="post.likes.user_likes !== 1 ? { cursor: 'pointer'}: {}"
                     :src="getAdjustedQualityImageUrl(post.attachments[0].photo.sizes)"
                     @click="post.likes.user_likes !== 1 && addLikeToPost(post.owner_id, post.post_id, index)"
                     alt=""
                />

                <PerfectScrollbar :class="{'pe-auto': showDetailedInfo === index }" class="ps-detailed-info">
                    <div class="detailed-info" :class="{ 'opacity-100': showDetailedInfo === index }">
                        <p class="mb-1" :class="columnSettings.fontClass" v-if="ownerDataById?.first_name">
                            <b>{{ ownerDataById?.first_name }} {{ ownerDataById?.last_name }}</b>
                        </p>

                        <p class="mb-1" :class="columnSettings.fontClass" v-if="ownerDataById?.name">
                            <b>{{ ownerDataById?.name }}</b>
                        </p>
                        <p class="mb-1" :class="columnSettings.fontClass" v-if="ownerDataById?.description">
                            <b>Описание:</b> {{ ownerDataById?.description }}
                        </p>

                        <p class="mb-1" :class="columnSettings.fontClass" v-if="ownerDataById?.last_seen?.time">
                            <b>Онлайн: </b> {{ date(ownerDataById?.last_seen?.time) }}
                        </p>

                        <p class="mb-1" :class="columnSettings.fontClass" v-if="ownerDataById?.status">
                            <b>Статус:</b> {{ ownerDataById?.status }}
                        </p>

                        <p class="mb-1" :class="columnSettings.fontClass" v-if="ownerDataById?.country?.title">
                            <b>Страна:</b> {{ ownerDataById?.country?.title }}
                        </p>

                        <p class="mb-1" :class="columnSettings.fontClass" v-if="ownerDataById?.city?.title">
                            <b>Город:</b> {{ ownerDataById?.city?.title }}
                        </p>

                        <p class="mb-1" :class="columnSettings.fontClass" v-if="ownerDataById?.friends_count">
                            <b>Друзья:</b> {{ ownerDataById?.friends_count }}
                        </p>

                        <p class="mb-1" :class="columnSettings.fontClass" v-if="ownerDataById?.followers_count">
                            <b>Подписчики:</b> {{ ownerDataById?.followers_count }}
                        </p>

                        <p class="mb-1" :class="columnSettings.fontClass" v-if="ownerDataById?.members_count">
                            <b>Подписчики:</b> {{ ownerDataById?.members_count }}
                        </p>
                    </div>
                </PerfectScrollbar>

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
        <component v-if="isOpen"
                   @mounted="showModal"
                   :is="modalComponent"
                   :owner-data="ownerDataById"
        />
    </Teleport>

</template>

<script setup>
    import { onMounted, onUnmounted, provide, ref, shallowRef } from 'vue'
    import { useAccountStore } from '@/stores/AccountStore'
    import { showErrorNotification } from '@/helpers/notyfHelper'
    import OwnerDetails from './Modals/OwnerDetails.vue'
    import { useRoute } from 'vue-router'
    import { debounce } from 'lodash'
    import { useModal } from '@/composables/useModal'

    const accountStore = useAccountStore()
    const route = useRoute()

    const ownerDataById = ref(null)
    const userId = ref('')
    const loadingStatus = ref([])
    const currentColumnClass = ref('col-4')
    const showNewsfeed = ref(true)
    const showDetailedInfo = ref(null)
    const showDetailedInfoButton = ref(null)
    const likedPostIndex = ref(null)
    const observer = ref(null)
    const modalComponent = shallowRef(null)
    const { isOpen, preparedModal, showModal, closeModal } = useModal()

    const iconClasses = ref({
        info: 'bi bi-info-circle',
        person: 'bi bi-person-fill',
        people: 'bi bi-people-fill'
    })

    const columnSettings = ref({
        columnClass: 'col-4',
        fontClass: 'fs-4'
    })

    const columnOptions = ref([
        { class: 'col-6', icon: 'bi-2-square', iconFill: 'bi-2-square-fill pe-none' },
        { class: 'col-4', icon: 'bi-3-square', iconFill: 'bi-3-square-fill pe-none' },
        { class: 'col-3', icon: 'bi-4-square', iconFill: 'bi-4-square-fill pe-none' }
    ])

    provide('closeModal', closeModal)

    const addLikeToPost = async (ownerId, itemId, index) => {
        likedPostIndex.value = index
        loadingStatus.value[index] = true

        await accountStore.addLike(userId.value, ownerId, itemId)
            .then(() => accountStore.accountNewsFeed[index].likes.user_likes = 1)
            .catch(error => showErrorNotification(error.message))
            .finally(() => (loadingStatus.value[index] = false))
    }

    const date = (timestamp) => new Date(timestamp * 1000).toLocaleTimeString('ru-RU')
    const hideDetailedInfo = () => (showDetailedInfo.value = false)
    const toggleDetailedInfoBtn = (index, show) => showDetailedInfoButton.value = show ? index : null

    const getAdjustedQualityImageUrl = (sizes) => {
        // Определения размеров изображений:
        // w - оригинал
        // z - выше среднего
        // x - ниже среднего
        // m - низкое качество
        const sizeMapping = {
            'col-6': ['w', 'z', 'x', 'm'], // Приоритеты размера для колонок шириной 6
            'col-4': ['z', 'x', 'm', 'w'], // Приоритеты размера для колонок шириной 4
            'col-3': ['x', 'm', 'z', 'w'] // Приоритеты размера для колонок шириной 3
        }

        // Получение массива предпочтительных размеров на основе текущего класса колонок
        const preferredSizes = sizeMapping[currentColumnClass.value] || ['m', 'x', 'z', 'w']

        // Поиск первого доступного предпочтительного размера изображения
        const foundSizeType = preferredSizes.find(sizeType => sizes.some(size => size.type === sizeType))

        // Возвращение URL изображения, если найден подходящий размер, иначе пустая строка
        return foundSizeType ? sizes.find(size => size.type === foundSizeType).url : ''
    }

    const ownerInfo = (accountId, index) => {
        showDetailedInfo.value = index
        ownerDataById.value = null

        const fetchData = accountId > 0
            ? accountStore.fetchOwnerData(userId.value, accountId)
            : accountStore.fetchGroupData(accountId)

        fetchData.then(() => ownerDataById.value = accountStore.getOwnerDataById(accountId))
            .catch(({ response }) => showErrorNotification(response.data.message))
    }

    const changeColumnClass = async (newClass) => {
        // Очищаем текущую ленту новостей и сбрасываем состояния пагинации
        accountStore.accountNewsFeed = []
        accountStore.nextFrom = null
        accountStore.previousNextFrom = null

        // Скрываем ленту новостей во время загрузки
        showNewsfeed.value = false

        // Обновляем текущий класс колонки
        currentColumnClass.value = newClass

        // Определяем класс шрифта на основе выбранного размера колонки
        switch (newClass) {
            case 'col-6':
                columnSettings.value.fontClass = 'fs-1' // Большие колонки -> Меньший шрифт
                columnSettings.value.columnClass = newClass
                break

            case 'col-4':
                columnSettings.value.fontClass = 'fs-4'
                columnSettings.value.columnClass = newClass
                break

            case 'col-3':
                columnSettings.value.fontClass = 'fs-5' // Меньшие колонки -> Больший шрифт
                columnSettings.value.columnClass = newClass
                break

            default:
                columnSettings.value.fontClass = 'fs-4' // Значение по умолчанию
        }
    }

    const loadMore = async () => {
        accountStore.isLoadingFeed = true
        await accountStore.fetchAccountNewsFeed(userId.value, accountStore.nextFrom)
            .then(() => showNewsfeed.value = true)
            .catch(() => showErrorNotification('Ошибка в loadMore()'))
    }

    const debounceLoadMore = debounce(() => loadMore(), 500, {
        'leading': true, // Вызываться в начале периода ожидания
        'trailing': false // Дополнительный вызов в конце периода не требуется
    })

    const showOwnerDetailsModal = (accountId, index) => {
        ownerInfo(accountId, index)
        modalComponent.value = preparedModal(OwnerDetails)
        showModal('ownerDetailsModal')
    }

    onMounted(() => {
        console.log('Newsfeed onMounted')

        userId.value = route.params.id
        accountStore.nextFrom = null
        accountStore.accountNewsFeed = []
        accountStore.isLoadingFeed = true

        observer.value = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    debounceLoadMore()
                }
            })
        }, { threshold: 0 })

        observer.value.observe(document.getElementById('loader'))
        loadingStatus.value = new Array(accountStore.accountNewsFeed.length).fill(false)
    })

    onUnmounted(() => {
        console.log('Newsfeed onUnmounted')
        if (observer.value) observer.value.disconnect()
    })
</script>

<style scoped lang="scss">
    .change-grid-columns {
        i {
            cursor: pointer;
            font-size: 2rem;
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

            .ps {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                pointer-events: none;
                border-radius: 6px;

                .detailed-info {
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    height: fit-content;
                    min-height: 100%;
                    padding: 2.4rem 2.6rem 2.6rem;
                    color: white;
                    opacity: 0;
                    background: rgba(0, 0, 0, 0.65);
                    transition: opacity .2s ease;
                    font-size: 1.3rem;
                }
            }
        }
    }

    #loader {
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
