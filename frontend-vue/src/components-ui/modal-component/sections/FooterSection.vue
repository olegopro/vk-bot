<script setup lang="ts">
  interface FooterSectionProps {
    onSubmit: () => void
    onCancel: () => void
    submitDisabled?: boolean
    cancelDisabled?: boolean
    isLoading?: boolean
    submitText?: string
    cancelText?: string
    loadingText?: string
    showCancel?: boolean
    loadingKey?: string
    store?: Record<string, unknown>
  }

  withDefaults(defineProps<FooterSectionProps>(), {
    submitDisabled: false,
    cancelDisabled: false,
    isLoading: false,
    submitText: 'Создать',
    cancelText: 'Отмена',
    loadingText: 'Создать',
    showCancel: true
  })
</script>

<template>
  <div class="modal-footer">
    <button
      v-if="showCancel"
      type="button"
      class="btn btn-secondary"
      :disabled="cancelDisabled"
      @click="onCancel"
    >
      {{ cancelText }}
    </button>
    <button
      type="button"
      class="btn btn-success"
      :disabled="submitDisabled || isLoading"
      @click="onSubmit"
    >
      {{ isLoading ? loadingText : submitText }}
      <span v-if="isLoading" class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span>
    </button>
  </div>
</template>
