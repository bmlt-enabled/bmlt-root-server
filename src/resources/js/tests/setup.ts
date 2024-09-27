import '@testing-library/jest-dom/vitest';

// @ts-expect-error set from backend
global.settings = {
  apiBaseUrl: '/',
  autoGeocodingEnabled: true,
  centerLongitude: -118.563659,
  centerLatitude: 34.235918,
  centerZoom: 6,
  countyAutoGeocodingEnabled: false,
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
