module.exports = {
  parserOptions: {
    ecmaVersion: 10,
    sourceType: 'module',
  },
  extends: ['plugin:prettier/recommended', 'eslint:recommended'],
  env: {
    browser: true,
    jquery: true,
    node: true,
  },
  globals: {
    API_URL: true,
    FB_PAGE_ID: true,
    FB_APP_ID: true,
    FILESTACK_KEY: true,
    FINGERPRINT_API_KEY: true,
    FINGERPRINT_STORAGE_KEY: true,
    RECAPTCHA_KEY: true,
  },
};
