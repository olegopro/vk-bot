<script setup>
  import { computed, inject, onMounted, onUnmounted } from 'vue'
  import Account from './OwnerDetails/Account.vue'
  import Group from './OwnerDetails/Group.vue'

  const props = defineProps(['ownerData'])
  const closeModal = inject('closeModal')

  const ownerType = computed(() => {
    if (props.ownerData?.type) {
      return 'group'
    } else if (props.ownerData?.first_name) {
      return 'account'
    }
    return null
  })

  const modalHide = () => closeModal('ownerDetailsModal')

  onMounted(() => console.log('OwnerDetails onMounted'))
  onUnmounted(() => console.log('OwnerDetails onUnmounted'))
</script>

<template>
  <div class="modal fade" id="ownerDetailsModal" tabindex="-1" aria-labelledby="Owner details" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">

        <Account v-if="ownerType === 'account'" :accountData="ownerData" />
        <Group v-if="ownerType === 'group'" :groupData="ownerData" />

        <!-- Добавленный modal-footer -->
        <div class="modal-footer">
          <slot name="modal-footer">
            <!-- Содержимое по умолчанию для слота modal-footer, если вдруг потребители компонента не предоставят своего -->
            <button class="btn btn-secondary" @click="modalHide">Закрыть</button>
          </slot>
        </div>

      </div>
    </div>
    </div>
</template>

<style lang="scss" scoped>
  :deep(img) {
    width: 200px;
    height: 200px;
    border-radius: 0.5rem;
  }

  :deep(.online-status) {
    position: absolute;
    right: 0;
  }

  :deep(.stub) {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 200px;
    height: 200px;
    border-right: 1px solid whitesmoke;
  }
</style>
