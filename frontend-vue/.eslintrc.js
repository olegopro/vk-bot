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
    '@vue/typescript/recommended',
    'plugin:import/recommended',
    'plugin:import/typescript'

    // '@vue/typescript'
  ],

  plugins: ['no-relative-import-paths'],

  parserOptions: {
    parser: '@typescript-eslint/parser',
    ecmaVersion: 2020,
    sourceType: 'module'
  },

  settings: {
    'import/resolver': {
      typescript: {
        alwaysTryTypes: true,
        project: './tsconfig.json'
      },
      node: {
        extensions: ['.js', '.jsx', '.ts', '.tsx', '.vue']
      }
    }
  },

  rules: {
    'no-console': process.env.NODE_ENV === 'production' ? 'warn' : 'off',
    'no-debugger': process.env.NODE_ENV === 'production' ? 'warn' : 'off',
    'space-before-function-paren': 'off',
    'vue/require-explicit-emits': 'error',
    'vue/multi-word-component-names': 'off',
    'no-return-assign': 'off',
    'func-call-spacing': 'off',
    'no-trailing-spaces': 'off',
    '@typescript-eslint/no-explicit-any': 'error',
    // Настройка отступов для тега <script> в Vue файлах
    'vue/script-indent': ['error', 2, {
      baseIndent: 1,
      switchCase: 1
    }],
    // Настройка отступов для тега <template> в файлах Vue
    'vue/html-indent': ['error', 2, {
      baseIndent: 1,
      attribute: 1,
      closeBracket: 0,
      alignAttributesVertically: false,
      ignores: []
    }],
    'vue/component-tags-order': ['error', {
      order: ['script', 'template', 'style']
    }],
    // Автоматическая замена относительных импортов на алиасы @/
    'no-relative-import-paths/no-relative-import-paths': ['error', {
      allowSameFolder: false,
      rootDir: 'src',
      prefix: '@'
    }],
    // Отключаем предупреждение о named exports как default imports
    'import/no-named-as-default': 'off'
  },

  overrides: [
    {
      files: ['*.js'],
      parserOptions: {
        parser: '@babel/eslint-parser',
        requireConfigFile: false
      }
    },
    {
      files: ['*.vue'],
      rules: {
        indent: 'off' // Отключаем стандартное правило для Vue файлов
      }
    }
  ]
}
