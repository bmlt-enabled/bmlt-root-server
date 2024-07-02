import '@testing-library/jest-dom';

// @ts-expect-error set from backend
global.settings = {
  apiBaseUrl: 'http://localhost:8000',
  defaultLanguage: 'en',
  isLanguageSelectorEnabled: true,
  languageMapping: {
    en: 'English',
    fr: 'French'
  },
  version: '1.0.0'
};
