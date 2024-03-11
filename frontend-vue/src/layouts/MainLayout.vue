<template>
    <PerfectScrollbar>
        <main>
            <div class="container">
                <TheNavbar />
                <RouterView />
            </div>
        </main>
    </PerfectScrollbar>
</template>

<script>
    import TheNavbar from '../components/TheNavbar.vue'

    export default {
        name: 'MainLayout',
        components: { TheNavbar }
    }
</script>

<style lang="scss" scoped>
    .ps {
        height: 100vh;
    }
</style>
