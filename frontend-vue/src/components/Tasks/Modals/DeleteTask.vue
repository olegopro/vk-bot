<template>
    <div class="modal fade" id="deleteTask" tabindex="-1" aria-labelledby="Delete task" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form @submit.prevent class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="Delete task"></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Удалить задачу c id <strong>{{ taskId }}</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" @click="deleteTask(taskId)" class="btn btn-danger">Удалить</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import { mapActions } from 'vuex'

    export default {
        props: ['taskId'],

        methods: {
            ...mapActions('tasks', ['deleteTask'])
        }
    }
</script>

<style scoped lang="scss">

</style>
