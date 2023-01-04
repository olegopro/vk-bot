module.exports = {
    root: true,
    env: {
        node: true
    },
    extends: [
        'plugin:vue/vue3-essential',
        '@vue/standard'
    ],
    parserOptions: {
        parser: '@babel/eslint-parser'
    },
    rules: {
        'no-console': process.env.NODE_ENV === 'production' ? 'warn' : 'off',
        'no-debugger': process.env.NODE_ENV === 'production' ? 'warn' : 'off',
        'no-tabs': 'off',
        'indent': 'off',
        'space-before-function-paren': 'off',
        'no-trailing-spaces': 'off',
        'vue/require-explicit-emits': 'error',
        'vue/multi-word-component-names': 'off'
    }

}
