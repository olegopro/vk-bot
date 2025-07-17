<script>

  import MainLayout from './layouts/MainLayout.vue'
  import GlobalModal from './components/GlobalModal.vue'

  export default {
    components: {
      MainLayout,
      GlobalModal
    },

    computed: {
      layout() {
        return this.$route.meta.layout + '-layout'
      }
    }
  }
</script>

<template>
  <component :is="layout" />
  <GlobalModal />
</template>
