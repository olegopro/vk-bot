<template>
    <div class="modal fade" id="deleteTask" tabindex="-1" aria-labelledby="Delete task" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="Delete task">Удаление задачи</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Удалить задачу</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-danger">Удалить</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {}
</script>

<style scoped lang="scss">

</style>
