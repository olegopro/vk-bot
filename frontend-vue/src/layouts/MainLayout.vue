<script>
  import NavbarComponent from '@/components-ui/navbar-component/NavbarComponent.vue'

  export default {
    name: 'MainLayout',
    components: { NavbarComponent }
  }
</script>

<template>
  <PerfectScrollbar>
    <main>
      <div class="container">
        <NavbarComponent />
        <RouterView />
      </div>
    </main>
  </PerfectScrollbar>
</template>

<style lang="scss" scoped>
    .ps {
        height: 100vh;
    }
</style>
