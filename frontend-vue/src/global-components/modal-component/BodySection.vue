<script setup lang="ts">
// BodySection component - универсальный компонент для модальных окон с PerfectScrollbar
</script>

<template>
  <PerfectScrollbar class="ps-modal">
    <div class="modal-body py-0">
      <slot />
    </div>
  </PerfectScrollbar>
</template>
