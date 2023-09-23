<template>
    <span :class="['badge', className]">
		{{ text }}
	</span>
</template>

<script>
    export default {
        props: ['type'],

        data() {
            return {
                classMap: {
                    active: 'text-bg-primary',
                    cancelled: 'text-bg-danger',
                    failed: 'text-bg-danger',
                    done: 'text-bg-success',
                    pending: 'text-bg-secondary'
                },

                textMap: {
                    active: 'Выполняется',
                    failed: 'Ошибка',
                    cancelled: 'Отменена',
                    done: 'Выполнена',
                    pending: 'Ожидание'
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
