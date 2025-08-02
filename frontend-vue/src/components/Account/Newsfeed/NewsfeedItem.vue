<script setup>
  import { ref, toRefs } from 'vue'
  import { useAccountStore } from '../../../stores/AccountStore'
  import { showErrorNotification, showSuccessNotification } from '../../../helpers/notyfHelper'
  import { useImageUrl } from '../../../composables/useImageUrl'
  import { useModal } from '../../../composables/useModal'
  import AccountDetailsModal from '../Modals/AccountDetailsModal.vue'
  import GroupDetailsModal from '../Modals/GroupDetailsModal.vue'

  const props = defineProps({
    index: Number,
    post: Object,
    currentColumnClass: String,
    loadingStatus: Array,
    iconClasses: Object,
    userId: String
  })

  const emit = defineEmits(['update-like'])

  const {
    post,
    index,
    currentColumnClass,
    loadingStatus,
    iconClasses,
    userId
  } = toRefs(props)

  const likedPostIndex = ref(null)

  const accountStore = useAccountStore()
  const { getAdjustedQualityImageUrl } = useImageUrl()
  const { showModal } = useModal()

  const addLikeToPost = async (ownerId, itemId, index) => {
    likedPostIndex.value = index
    loadingStatus.value[index] = true

    await accountStore.addLike.execute({
      likeData: {
        account_id: userId.value,
        owner_id: ownerId,
        item_id: itemId
      }
    })
      .then(response => {
        emit('update-like', index)
        showSuccessNotification(response.message)
      })
      .catch(error => showErrorNotification(error.message))
      .finally(() => (loadingStatus.value[index] = false))
  }

  const showOwnerDetailsModal = async (accountId) => {
    if (accountId < 0) {
      // Отрицательный ID - это группа
      await accountStore.fetchGroupData.execute({ groupId: Math.abs(accountId) })
      await showModal(GroupDetailsModal)
    } else {
      // Положительный ID - это пользователь
      await accountStore.fetchOwnerData.execute({ accountId: userId.value, ownerId: accountId })
      await showModal(AccountDetailsModal)
    }
  }
</script>

<template>
  <div v-masonry-tile :class="[currentColumnClass, 'item', 'mb-4', 'placeholder-glow']">
    <button class="account-info-btn mr-1"
      @click="showOwnerDetailsModal(post.source_id)"
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
    >
      <img class="card-img-top"
        :style="post.likes.user_likes !== 1 ? { cursor: 'pointer'}: {}"
        :src="getAdjustedQualityImageUrl(post.attachments[0].photo.sizes, currentColumnClass)"
        @click="post.likes.user_likes !== 1 && addLikeToPost(post.owner_id, post.post_id, index)"
        alt=""
      />
    </div>
  </div>
</template>

<style lang="scss" scoped>
  .item {
    &:hover {
      button.account-info-btn {
        opacity: 1;
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
    }
  }
</style>
