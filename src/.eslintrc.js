module.exports = {
  env: {
    browser: true,
    es2021: true,
  },
  extends: [
    'airbnb',
    'prettier',
  ],
  overrides: [
    {
      files: ['*.js'],
      rules: {
        'max-classes-per-file': 'off',
        'import/prefer-default-export': 'off',
      },
    },
  ],
  parserOptions: {
    ecmaVersion: 'latest',
    sourceType: 'module',
  },
  plugins: [
    'prettier',
  ],
  rules: {
    'prettier/prettier': 'error',
  },
};
