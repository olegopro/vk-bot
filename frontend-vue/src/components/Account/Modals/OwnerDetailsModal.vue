<script setup lang="ts">
  import { computed, getCurrentInstance } from 'vue'
  import { useModal } from '@/composables/useModal'
  import { OwnerData } from '@/models/AccountsModel'
  import Account from './OwnerDetails/Account.vue'
  import Group from './OwnerDetails/Group.vue'

  const modalId = getCurrentInstance()?.type.__name
  const { closeModal } = useModal()

  const { ownerData } = defineProps<{
    ownerData: OwnerData
  }>()

  const ownerType = computed(() => {
    if (ownerData?.type) {
      return 'group'
    } else if (ownerData?.first_name) {
      return 'account'
    }
    return null
  })
</script>

<template>
  <div class="modal fade" :id="modalId" tabindex="-1" aria-labelledby="Owner details" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5">Детали владельца</h1>
          <button type="button" class="btn-close" @click="closeModal(modalId)" aria-label="Close"></button>
        </div>
        
        <div class="modal-body">
          <Account v-if="ownerType === 'account'" :accountData="ownerData" />
          <Group v-if="ownerType === 'group'" :groupData="ownerData" />
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeModal(modalId)">Закрыть</button>
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
