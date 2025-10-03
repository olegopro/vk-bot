<script>

  import MainLayout from './layouts/MainLayout.vue'
  import ModalComponent from './components-ui/modal-component/ModalComponent.vue'

  export default {
    components: {
      MainLayout,
      ModalComponent
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
