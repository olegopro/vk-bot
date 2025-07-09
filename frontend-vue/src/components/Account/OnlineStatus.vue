<script>
  export default {
    props: ['type'],

    data() {
      return {
        classMap: {
          online: 'text-bg-success',
          offline: 'text-bg-danger'
        },

        textMap: {
          online: 'В сети',
          offline: 'Оффлайн'
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

<template>
  <span :class="['badge float-end', className]">
    {{ text }}
  </span>
</template>

<style scoped>

</style>
