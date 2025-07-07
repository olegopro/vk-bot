module.exports = {
    root: true,

    env: {
        node: true
    },

    extends: [
        'plugin:vue/vue3-essential',
        '@vue/standard',
        // Это нужно поддержки TypeScript в ESLint
        'plugin:@typescript-eslint/recommended',
        // При использовании TypeScript с Vue
        '@vue/typescript/recommended'

        // '@vue/typescript'
    ],

    parserOptions: {
        parser: '@typescript-eslint/parser',
        ecmaVersion: 2020,
        sourceType: 'module'
    },

    rules: {
        'no-console': process.env.NODE_ENV === 'production' ? 'warn' : 'off',
        'no-debugger': process.env.NODE_ENV === 'production' ? 'warn' : 'off',
        'no-tabs': 'off',
        'indent': 'off',
        'space-before-function-paren': 'off',
        'no-trailing-spaces': 'off',
        'vue/require-explicit-emits': 'error',
        'vue/multi-word-component-names': 'off',
        'no-return-assign': 'off',
        'quote-props': 'off',
        'no-unused-vars': 'warn',
        'no-mixed-spaces-and-tabs': 'off',
        // Изменяем правило для any с error на warning
        '@typescript-eslint/no-explicit-any': 'warn'
    },

    // Добавляем overrides для того, чтобы сохранить совместимость с существующим JavaScript кодом
    overrides: [
        {
            files: ['*.js'], // Определяем для каких файлов будут применяться эти настройки
            parserOptions: {
                parser: '@babel/eslint-parser'
            }
        }
    ]
}
