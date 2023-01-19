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
                    done: 'text-bg-success',
                    pending: 'text-bg-secondary'
                },

                textMap: {
                    active: 'Выполняется',
                    cancelled: 'Ошибка',
                    done: 'Выполнена',
                    pending: 'Выполняется'
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

</style>
