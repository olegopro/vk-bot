<template>
    <main>
        <div class="container">
            <TheNavbar />
            <router-view></router-view>
        </div>
    </main>
</template>

<script>
    import TheNavbar from '../components/TheNavbar.vue'

    export default {
        name: 'MainLayout',
        components: { TheNavbar }
    }
</script>
