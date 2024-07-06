import '@testing-library/jest-dom/vitest';

// @ts-expect-error set from backend
global.settings = {
  apiBaseUrl: '/',
  defaultLanguage: 'en',
  isLanguageSelectorEnabled: true,
  languageMapping: {
    en: 'English',
    de: 'Deutsch',
    fr: 'Français'
  },
  version: '1.0.0'
};
