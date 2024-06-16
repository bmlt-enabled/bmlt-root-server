import * as utils from './LocalizedStringsUtils';

// Cribbed from https://github.com/stefalda/react-localization

interface LocalizedStringsOptions {
  customLanguageInterface?: () => string;
  pseudo?: boolean;
  pseudoMultipleLanguages?: boolean;
  logsEnabled?: boolean;
}

interface LocalizedStringsProps {
  [key: string]: any;
}

const placeholderReplaceRegex = /(\{[|\w]+})/;
const placeholderReferenceRegex = /(\$ref\{[\w|.]+})/;

export default class LocalizedStrings {
  private _opts: LocalizedStringsOptions;
  private readonly _interfaceLanguage: string;
  private _language: string;
  private _defaultLanguage!: string;
  private _defaultLanguageFirstLevelKeys!: string[];
  private _props!: LocalizedStringsProps;
  private _availableLanguages?: string[];

  /**
   * Constructor used to provide the strings objects in various language and the optional callback to get
   * the interface language
   * @param props - the strings object
   * @param options - the options for custom language interface, pseudo translation, etc.
   */
  constructor(props: LocalizedStringsProps, options?: LocalizedStringsOptions | (() => string)) {
    if (typeof options === 'function') {
      options = { customLanguageInterface: options };
    }
    this._opts = Object.assign(
      {},
      {
        customLanguageInterface: utils.getInterfaceLanguage,
        pseudo: false,
        pseudoMultipleLanguages: false,
        logsEnabled: true
      },
      options
    );
    this._interfaceLanguage = this._opts.customLanguageInterface ? this._opts.customLanguageInterface() : utils.getInterfaceLanguage();
    this._language = this._interfaceLanguage;
    this.setContent(props);
  }

  /**
   * Set the strings objects based on the parameter passed in the constructor
   * @param props
   */
  setContent(props: LocalizedStringsProps): void {
    const [defaultLang] = Object.keys(props);
    this._defaultLanguage = defaultLang;
    this._defaultLanguageFirstLevelKeys = [];
    // Store locally the passed strings
    this._props = props;
    utils.validateTranslationKeys(Object.keys(props[this._defaultLanguage]));
    // Store first level keys (for identifying missing translations)
    Object.keys(this._props[this._defaultLanguage]).forEach((key) => {
      if (typeof this._props[this._defaultLanguage][key] === 'string') {
        this._defaultLanguageFirstLevelKeys.push(key);
      }
    });
    // Set language to its default value (the interface)
    this.setLanguage(this._interfaceLanguage);
    // Developer mode with pseudo
    if (this._opts.pseudo) {
      this._pseudoAllValues(this._props);
    }
  }

  /**
   * Replace all strings to pseudo value
   * @param obj - Loopable object
   */
  private _pseudoAllValues(obj: LocalizedStringsProps): void {
    Object.keys(obj).forEach((property) => {
      if (typeof obj[property] === 'object') {
        this._pseudoAllValues(obj[property]);
      } else if (typeof obj[property] === 'string') {
        if (obj[property].indexOf('[') === 0 && obj[property].lastIndexOf(']') === obj[property].length - 1) {
          // already pseudo fixed
          return;
        }
        const strArr = obj[property].split(' ');
        for (let i = 0; i < strArr.length; i += 1) {
          if (strArr[i].match(placeholderReplaceRegex)) {
            // we want to keep this string, includes specials
          } else if (strArr[i].match(placeholderReferenceRegex)) {
            // we want to keep this string, includes specials
          } else {
            let len = strArr[i].length;
            if (this._opts.pseudoMultipleLanguages) {
              len = Math.floor(len * 1.4); // add length with 40%
            }
            strArr[i] = utils.randomPseudo(len);
          }
        }
        obj[property] = `[${strArr.join(' ')}]`;
      }
    });
  }

  /**
   * Can be used from outside the class to force a particular language
   * independently of the interface one
   * @param language
   */
  setLanguage(language: string): void {
    const bestLanguage = utils.getBestMatchingLanguage(language, this._props);
    const defaultLanguage = Object.keys(this._props)[0];
    this._language = bestLanguage;
    if (this._props[bestLanguage]) {
      for (let i = 0; i < this._defaultLanguageFirstLevelKeys.length; i += 1) {
        delete (this as any)[this._defaultLanguageFirstLevelKeys[i]];
      }
      let localizedStrings = Object.assign({}, this._props[this._language]);
      Object.keys(localizedStrings).forEach((key) => {
        (this as any)[key] = localizedStrings[key];
      });
      if (defaultLanguage !== this._language) {
        localizedStrings = this._props[defaultLanguage];
        this._fallbackValues(localizedStrings, this);
      }
    }
  }

  /**
   * Load fallback values for missing translations
   * @param defaultStrings
   * @param strings
   */
  private _fallbackValues(defaultStrings: LocalizedStringsProps, strings: any): void {
    Object.keys(defaultStrings).forEach((key) => {
      if (Object.prototype.hasOwnProperty.call(defaultStrings, key) && (!strings[key] || strings[key] === '')) {
        strings[key] = defaultStrings[key];
        if (this._opts.logsEnabled) {
          console.log(`🚧 👷 key '${key}' not found or empty in localizedStrings for language ${this._language} 🚧`);
        }
      } else if (typeof strings[key] !== 'string') {
        this._fallbackValues(defaultStrings[key], strings[key]);
      }
    });
  }

  /**
   * The current language displayed (could differ from the interface language
   * if it has been forced manually and a matching translation has been found)
   */
  getLanguage(): string {
    return this._language;
  }

  /**
   * The current interface language (could differ from the language displayed)
   */
  getInterfaceLanguage(): string {
    return this._interfaceLanguage;
  }

  /**
   * Return an array containing the available languages passed as props in the constructor
   */
  getAvailableLanguages(): string[] {
    if (!this._availableLanguages) {
      this._availableLanguages = [];
      Object.keys(this._props).forEach((key) => {
        this._availableLanguages!.push(key);
      });
    }
    return this._availableLanguages;
  }

  /**
   * Format the passed string replacing the numbered or tokenized placeholders
   * @param str - The string to format
   * @param valuesForPlaceholders - The values to replace in the string
   */
  formatString(str: string, ...valuesForPlaceholders: (string | Record<string, any>)[]): string {
    let input = str || '';
    input = this.getString(str, undefined, true) || input;
    const ref = input
      .split(placeholderReferenceRegex)
      .filter((textPart) => !!textPart)
      .map((textPart) => {
        if (textPart.match(placeholderReferenceRegex)) {
          const matchedKey = textPart.slice(5, -1);
          const referenceValue = this.getString(matchedKey);
          if (referenceValue) return referenceValue;
          if (this._opts.logsEnabled) {
            console.log(`No Localization ref found for '${textPart}' in string '${str}'`);
          }
          return `$ref(id:${matchedKey})`;
        }
        return textPart;
      })
      .join('');
    return ref
      .split(placeholderReplaceRegex)
      .filter((textPart) => !!textPart)
      .map((textPart) => {
        if (textPart.match(placeholderReplaceRegex)) {
          const matchedKey = textPart.slice(1, -1);
          let valueForPlaceholder = (valuesForPlaceholders as any)[matchedKey];
          if (valueForPlaceholder === undefined) {
            const valueFromObjectPlaceholder = (valuesForPlaceholders[0] as Record<string, any>)[matchedKey];
            if (valueFromObjectPlaceholder !== undefined) {
              valueForPlaceholder = valueFromObjectPlaceholder;
            } else {
              return valueForPlaceholder;
            }
          }
          return valueForPlaceholder;
        }
        return textPart;
      })
      .join('');
  }

  /**
   * Return a string with the passed key in a different language or default if not set
   * We allow deep . notation for finding strings
   * @param key - The key of the string
   * @param language - The language of the string
   * @param omitWarning - Whether to omit warning if string is not found
   */
  getString(key: string, language?: string, omitWarning = false): string | null {
    try {
      let current = this._props[language || this._language];
      const paths = key.split('.');
      for (let i = 0; i < paths.length; i += 1) {
        if (current[paths[i]] === undefined) {
          throw new Error(paths[i]);
        }
        current = current[paths[i]];
      }
      return current;
    } catch (ex) {
      if (!omitWarning && this._opts.logsEnabled) {
        if (ex instanceof Error) {
          console.log(`No localization found for key '${key}' and language '${language}', failed on ${ex.message}`);
        } else {
          console.log(`No localization found for key '${key}' and language '${language}', and an unknown error occurred.`);
        }
      }
    }
    return null;
  }

  /**
   * The current props (locale object)
   */
  getContent(): LocalizedStringsProps {
    return this._props;
  }
}
