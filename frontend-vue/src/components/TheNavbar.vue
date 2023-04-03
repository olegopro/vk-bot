<template>
    <div class="row">
       <div class="col-12">
           <nav class="nav nav-pills nav-fill">
               <router-link to="/" :class="['nav-link', addActiveClass]">Аккаунты</router-link>
               <router-link to="/tasks" class="nav-link">Задачи</router-link>
               <router-link to="/statistics" class="nav-link">Статистика</router-link>
               <router-link to="/settings" class="nav-link">Настройки</router-link>
           </nav>
       </div>
    </div>
</template>

<script>
    export default {
        name: 'TheNavbar',

        computed: {
            addActiveClass() {
                return this.$route.name === 'Account'
                    ? 'active'
                    : ''
            }
        }
    }
</script>

<style scoped lang="scss">
    nav {
        padding-top: 30px;
        padding-bottom: 30px;

        .nav-link {
            color: #636363;

            &:hover {
                color: #000000;

                &.active {
                    color: white
                }
            }
        }
    }
</style>
