<template>
    <main class="container">
        <TheNavbar />
        <router-view />
    </main>
</template>

<script>
    import TheNavbar from '../components/TheNavbar.vue'

    export default {
        name: 'MainLayout',
        components: { TheNavbar }
    }
</script>
