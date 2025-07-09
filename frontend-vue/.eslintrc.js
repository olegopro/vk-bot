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
    'space-before-function-paren': 'off',
    'vue/require-explicit-emits': 'error',
    'vue/multi-word-component-names': 'off',
    'no-return-assign': 'off',
    // Изменяем правило для any с error на warning
    '@typescript-eslint/no-explicit-any': 'warn',
    // Настройка отступов для тега <script> в Vue файлах
    'vue/script-indent': ['error', 2, {
      baseIndent: 1,
      switchCase: 1
    }],
    // Настройка отступов для тега <template> в Vue файлах
    'vue/html-indent': ['error', 2, {
      baseIndent: 1,
      attribute: 1,
      closeBracket: 0,
      alignAttributesVertically: false,
      ignores: []
    }],
    'vue/component-tags-order': ['error', {
      order: ['script', 'template', 'style']
    }]
  },

  overrides: [
    {
      files: ['*.js'],
      parserOptions: {
        parser: '@babel/eslint-parser'
      }
    },
    {
      files: ['*.vue'],
      rules: {
        'indent': 'off' // Отключаем стандартное правило для Vue файлов
      }
    }
  ]
}
