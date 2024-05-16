<template>
    <div class="row justify-content-end mt-5 mb-3">
        <div class="col-3 d-flex justify-content-end change-grid-columns">
            <i :class="[
                    columnSettings.columnClass === option.class ? option.iconFill : option.icon,'bi','me-2',
                    accountStore.isLoadingFeed ? 'pe-none' : ''
               ]"
               v-for="option in columnOptions"
               :key="option.class"
               @click="changeColumnClass(option.class)"
            />
        </div>
    </div>

    <div v-masonry item-selector=".item" v-if="showNewsfeed" transition-duration="0s" class="row">
        <NewsfeedItem
            v-for="(post, index) in accountStore.accountNewsFeed"
            :key="index"
            :index="index"
            :post="post"
            :current-column-class="currentColumnClass"
            :loading-status="loadingStatus"
            :icon-classes="iconClasses"
            :column-settings="columnSettings"
            :user-id="userId"
            @showOwnerDetailsModal="handleShowOwnerDetailsModal"
        />
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
    import { useRoute } from 'vue-router'
    import { debounce } from 'lodash'
    import { useModal } from '@/composables/useModal'
    import NewsfeedItem from './Newsfeed/NewsfeedItem.vue'
    import OwnerDetails from './Modals/OwnerDetails.vue'

    const accountStore = useAccountStore()
    const route = useRoute()

    let observer = null
    const userId = ref('')
    const loadingStatus = ref([])
    const currentColumnClass = ref('col-4')
    const showNewsfeed = ref(true)
    const modalComponent = shallowRef(null)
    const ownerDataById = ref(null)
    const showDetailedInfo = ref(null)
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

    const handleShowOwnerDetailsModal = ({ accountId, index }) => {
        ownerInfo(accountId, index)
        modalComponent.value = preparedModal(OwnerDetails)
        showModal('ownerDetailsModal')
    }

    onMounted(() => {
        userId.value = route.params.id
        accountStore.nextFrom = null
        accountStore.accountNewsFeed = []
        accountStore.isLoadingFeed = true

        observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    debounceLoadMore()
                }
            })
        }, { threshold: 0 })

        observer.observe(document.getElementById('loader'))
        loadingStatus.value = new Array(accountStore.accountNewsFeed.length).fill(false)
    })

    onUnmounted(() => {
        if (observer) observer.disconnect()
    })
</script>

<style scoped lang="scss">
    .change-grid-columns {
        i {
            cursor: pointer;
            font-size: 2rem;
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
