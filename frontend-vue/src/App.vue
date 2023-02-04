<template>
    <component :is="layout" />
</template>

<script>
    import MainLayout from './layouts/MainLayout.vue'

    export default {
        components: {
            MainLayout
        },

        computed: {
            layout() {
                return this.$route.meta.layout + '-layout'
            }
        }
    }
</script>
