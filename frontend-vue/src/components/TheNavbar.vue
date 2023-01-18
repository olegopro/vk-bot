<template>
    <nav class="nav nav-pills nav-fill">
        <router-link to="/" class="nav-link">Аккаунты</router-link>
        <router-link to="tasks" class="nav-link">Задачи</router-link>
        <router-link to="history" class="nav-link">История</router-link>
        <router-link to="logs" class="nav-link">Лог</router-link>
    </nav>
</template>

<script>
    export default {
        name: 'TheNavbar'
    }
</script>

<style scoped>

</style>
