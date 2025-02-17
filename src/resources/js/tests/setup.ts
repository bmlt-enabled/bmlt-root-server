import '@testing-library/jest-dom/vitest';

// @ts-expect-error set from backend
global.settings = {
  apiBaseUrl: '/',
  bmltTitle: '',
  autoGeocodingEnabled: true,
  centerLongitude: -118.563659,
  centerLatitude: 34.235918,
  centerZoom: 6,
  countyAutoGeocodingEnabled: false,
  customFields: [{ name: 'zone', displayName: 'zone', language: 'en' }],
  defaultClosedStatus: true,
  defaultDuration: '01:00:00',
  defaultLanguage: 'en',
  distanceUnits: 'mi',
  googleApiKey: '',
  isLanguageSelectorEnabled: true,
  languageMapping: {
    en: 'English',
    de: 'Deutsch',
    fr: 'Français'
  },
  meetingStatesAndProvinces: [],
  meetingCountiesAndSubProvinces: [],
  regionBias: 'us',
  version: '1.0.0',
  zipAutoGeocodingEnabled: false
};

global.window.matchMedia = vi.fn().mockImplementation((query) => ({
  matches: false,
  media: query,
  onchange: null,
  addListener: vi.fn(),
  removeListener: vi.fn(),
  addEventListener: vi.fn(),
  removeEventListener: vi.fn(),
  dispatchEvent: vi.fn()
}));
