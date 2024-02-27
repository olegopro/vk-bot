<template>
    <span :class="['badge', className]">
		{{ text }}
        <i v-if="errorMessage" class="bi bi-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" :data-bs-title="errorMessage" />
	</span>
</template>

<script>
    import { Tooltip } from 'bootstrap'

    export default {
        props: ['type', 'errorMessage'],

        data() {
            return {
                classMap: {
                    active: 'text-bg-primary',
                    canceled: 'text-bg-danger',
                    failed: 'text-bg-danger',
                    done: 'text-bg-success',
                    pending: 'text-bg-secondary',
                    queued: 'text-bg-secondary',
                    pause: 'text-bg-secondary'
                },

                textMap: {
                    active: 'Выполняется',
                    failed: 'Ошибка',
                    canceled: 'Отмена',
                    done: 'Выполнена',
                    pending: 'Обрабатывается',
                    queued: 'Ожидание',
                    pause: 'На паузе'
                }
            }
        },

        computed: {
            className() {
                return this.classMap[this.type]
            },

            text() {
                return this.textMap[this.type]
            }
        },

        mounted() {
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            [...tooltipTriggerList].map(tooltipTriggerEl => new Tooltip(tooltipTriggerEl))
        }
    }
</script>

<style scoped lang="scss">
    span {
        line-height: 12px;
        padding: 0.4rem;
        width: fit-content;
        user-select: none;
        cursor: default;
    }
</style>
