<script setup lang="ts">
  import { computed, onMounted, getCurrentInstance } from 'vue'
  import { useModal } from '@/composables/useModal'

  const { isOpen, currentComponent, currentProps, setGlobalModalRef } = useModal()

  const modalComponent = computed(() => currentComponent.value)
  const modalProps = computed(() => currentProps.value || {})

  onMounted(() => setGlobalModalRef(getCurrentInstance()))
</script>

<template>
  <Teleport to="body">
    <component
      v-if="isOpen && modalComponent"
      :is="modalComponent"
      v-bind="modalProps"
      ref="modalComponentRef"
    />
  </Teleport>
</template>
