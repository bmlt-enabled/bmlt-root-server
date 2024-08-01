import '@testing-library/jest-dom/vitest';
import { vi } from 'vitest';

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

if (typeof window.URL.createObjectURL === 'undefined') {
  window.URL.createObjectURL = vi.fn();
}
