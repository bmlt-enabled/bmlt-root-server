declare global {
  const apiBaseUrl: string;
  const defaultLanguage: string;
  const isLanguageSelectorEnabled: boolean;
  const languageMapping: Record<string, string>;
  const currentVersion: string;
}

export {};
