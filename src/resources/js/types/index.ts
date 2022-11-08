declare global {
  const settings: {
    apiBaseUrl: string;
    defaultLanguage: string;
    isLanguageSelectorEnabled: boolean;
    languageMapping: Record<string, string>;
    version: string;
  };
}

export {};
