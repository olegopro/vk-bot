<template>
    <div class="modal fade" id="deleteTask" tabindex="-1" aria-labelledby="Delete task" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form @submit.prevent="deleteTaskById" class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="Delete task">Удаление задачи</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Удалить задачу c ID <strong>{{ taskId }}</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="modalHide">Отмена</button>
                    <button type="submit" class="btn btn-danger" :disabled="disable">Удалить</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import { mapActions } from 'vuex'
    import { Modal } from 'bootstrap'

    export default {
        props: ['taskId'],

        data() {
            return {
                modal: null,
                disable: false
            }
        },

        mounted() {
            this.modal = new Modal(document.getElementById('deleteTask'))
        },

        methods: {
            ...mapActions('tasks', ['deleteTask']),

            deleteTaskById() {
                this.disable = true

                this.deleteTask(this.taskId)
                    .then(() => {
                        this.modal.hide()
                        this.disable = false
                    })
            },

            modalHide() {
                this.modal.hide()
            }
        }
    }
</script>

<style scoped lang="scss">

</style>
