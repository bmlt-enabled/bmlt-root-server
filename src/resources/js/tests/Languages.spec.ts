import { beforeAll, describe, expect, test } from 'vitest';
import { render, screen, waitFor } from '@testing-library/svelte';
import '@testing-library/jest-dom';
import userEvent from '@testing-library/user-event';

import App from '../App.svelte';
import { sharedAfterEach, sharedBeforeAll, sharedBeforeEach } from './sharedDataAndMocks';
import { translations } from '../stores/localization';

beforeAll(sharedBeforeAll);
beforeEach(sharedBeforeEach);

afterEach(async () => {
  await sharedAfterEach();
  // put the default language back to English in case a test changed it
  translations.setLanguage('en');
});

describe('language selection tests', () => {
  test('test language selection menu with a successful login', async () => {
    const user = userEvent.setup();
    render(App);
    const select_lang: HTMLSelectElement = await screen.findByRole('combobox', { name: 'Select Language' });
    expect(select_lang.length).toBe(4);
    expect(select_lang.item(0)?.label).toBe('Choose option ...');
    expect(select_lang.item(1)?.label).toBe('English');
    expect(select_lang.item(2)?.label).toBe('Deutsch');
    expect(select_lang.item(3)?.label).toBe('Français');
    await userEvent.selectOptions(select_lang, ['Deutsch']);
    // login screen should now be in German, including the Language Selection menu title
    expect(screen.getByRole('combobox', { name: 'Sprache auswählen' })).toBeEnabled();
    await user.type(screen.getByRole('textbox', { name: 'Benutzername' }), 'serveradmin');
    await user.type(screen.getByLabelText('Passwort'), 'serveradmin-password');
    expect(screen.getByRole('textbox', { name: 'Benutzername' })).toHaveDisplayValue('serveradmin');
    expect(screen.getByLabelText('Passwort')).toHaveDisplayValue('serveradmin-password');
    await user.click(screen.getByRole('button', { name: 'Anmelden' }));
    // after a successful login, we should see 'Willkommen Server Administrator' and the navbar
    expect(screen.getByText('Willkommen Server Administrator')).toBeInTheDocument();
    // check for one element in the navbar
    // TODO: shouldn't need to say hidden!
    expect(screen.getByRole('link', { name: 'Benutzer', hidden: true })).toBeEnabled();
    // Log out
    await user.click(screen.getByRole('link', { name: 'Abmelden', hidden: true }));
    // Make sure we're back at the login screen (which should still be in German)
    await waitFor(() => {
      expect(screen.getByRole('button', { name: 'Anmelden' })).toBeEnabled();
      expect(screen.getByRole('combobox', { name: 'Sprache auswählen' })).toBeEnabled();
    });
  });

  test('test language selection menu with a bad login', async () => {
    // just set the language directly for this test -- already tested the language selection menu above
    translations.setLanguage('de');
    const user = userEvent.setup();
    render(App);
    await user.type(await screen.findByRole('textbox', { name: 'Benutzername' }), 'serveradmin');
    await user.type(await screen.findByLabelText('Passwort'), 'schlechtes-passwort');
    await user.click(await screen.findByRole('button', { name: 'Anmelden' }));
    await screen.findByText('Ungültiger Benutzername oder Passwort.');
  });

  test('test isLanguageSelectorEnabled == false', async () => {
    const settings = (global as any).settings;
    settings['isLanguageSelectorEnabled'] = false;
    render(App);
    // TODO: Can this be simplified?  It seems like a baroque way to check that the language selector isn't present.
    let errorName = '';
    try {
      await screen.findByRole('combobox', { name: 'Select Language' });
    } catch (err: any) {
      errorName = err.name;
    }
    expect(errorName).toBe('TestingLibraryElementError');
  });
});

describe('translations tests', () => {
  test('test that the same keys exist for all languages', () => {
    const allTranslations = translations.getTranslationsForAllLanguages();
    const languages = Object.keys(allTranslations);
    const referenceLanguage = 'en';
    const referenceKeys = Object.keys(allTranslations[referenceLanguage]);
    languages.forEach((language) => {
      const currentKeys = Object.keys(allTranslations[language]);
      expect(currentKeys).toEqual(referenceKeys);
    });
  });
});
