const { defineConfig } = require('@vue/cli-service')
module.exports = defineConfig({
    transpileDependencies: true,
    devServer: {
        // host: 'localhost',
        client: {
            // overlay: false, // fix : ResizeObserver loop limit exceeded
            webSocketURL: 'ws://0.0.0.0:3000/ws'
        }
    }
})
