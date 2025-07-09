<script setup>
  import { ref, toRefs } from 'vue'
  import { useAccountStore } from '../../../stores/AccountStore'
  import { showErrorNotification } from '../../../helpers/notyfHelper'
  import { useImageUrl } from '../../../composables/useImageUrl'

  const props = defineProps({
    index: Number,
    post: Object,
    currentColumnClass: String,
    loadingStatus: Array,
    iconClasses: Object,
    columnSettings: Object,
    userId: String
  })

  const emit = defineEmits(['showOwnerDetailsModal'])

  const {
    post,
    index,
    currentColumnClass,
    loadingStatus,
    iconClasses,
    columnSettings,
    userId
  } = toRefs(props)

  const showDetailedInfo = ref(null)
  const showDetailedInfoButton = ref(null)
  const ownerDataById = ref(null)
  const likedPostIndex = ref(null)

  const accountStore = useAccountStore()
  const { getAdjustedQualityImageUrl } = useImageUrl()

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

  const emitShowOwnerDetailsModal = (accountId, index) => {
    emit('showOwnerDetailsModal', { accountId, index })
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
</script>

<template>
  <div v-masonry-tile
    :class="[currentColumnClass, 'item', 'mb-4', 'placeholder-glow']"
    @mouseleave="hideDetailedInfo"
  >
    <button class="account-info-btn mr-1"
      :class="{ 'opacity-100': showDetailedInfoButton === index || likedPostIndex === index }"
      @click="emitShowOwnerDetailsModal(post.source_id, index)"
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
        :src="getAdjustedQualityImageUrl(post.attachments[0].photo.sizes, currentColumnClass)"
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
</template>

<style lang="scss" scoped>
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
                    background: rgba(0, 0, 0, 0.29);
                    backdrop-filter: blur(5px);
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
</style>
