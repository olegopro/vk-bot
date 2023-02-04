<template>
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h1 class="h2">Статистика</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Аккаунт</th>
                    <th scope="col">Статус</th>
                    <th scope="col">Действия</th>
                    <th scope="col">Добавлен</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <th scope="row">id задачи</th>
                    <td>логин</td>
                    <td>
                        <TaskStatus :type="'active'" />
                    </td>
                    <td>
                        <router-link custom to="/" v-slot="{navigate}">
                            <a class="btn btn-primary me-2" @click="navigate">
                                <svg width="16" height="20">
                                    <use xlink:href="#info"></use>
                                </svg>
                            </a>
                        </router-link>

                    </td>
                    <td>20.10.2022</td>

                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
    import TaskStatus from '../components/Tasks/TaskStatus.vue'

    export default {
        components: { TaskStatus }
    }
</script>

<style scoped lang="scss">

</style>
