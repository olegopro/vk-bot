const { defineConfig } = require('@vue/cli-service')
module.exports = defineConfig({
  transpileDependencies: true,
  devServer: {
    // host: 'localhost',
    client: {
      webSocketURL: 'ws://0.0.0.0:3000/ws'
    }
  }
})
