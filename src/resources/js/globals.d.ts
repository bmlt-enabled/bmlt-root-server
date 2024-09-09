declare const settings: {
  apiBaseUrl: string;
  autoGeocodingEnabled: true;
  centerLongitude: number;
  centerLatitude: number;
  centerZoom: number;
  countyAutoGeocodingEnabled: boolean;
  defaultClosedStatus: boolean;
  defaultDuration: string;
  defaultLanguage: string;
  distanceUnits: string;
  googleApikey: string;
  isLanguageSelectorEnabled: boolean;
  languageMapping: Record<string, string>;
  meetingStatesAndProvinces: string[];
  meetingCountiesAndSubProvinces: string[];
  regionBias: string;
  version: string;
  zipAutoGeocodingEnabled: false;
};

declare module 'localized-strings' {
  export interface LocalizedStringsMethods {
    getContent(): any;
  }
}
