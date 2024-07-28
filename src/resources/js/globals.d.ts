declare const settings: {
  apiBaseUrl: string;
  defaultLanguage: string;
  isLanguageSelectorEnabled: boolean;
  languageMapping: Record<string, string>;
  version: string;
};

declare module 'localized-strings' {
  export interface LocalizedStringsMethods {
    getContent(): any;
  }
}
