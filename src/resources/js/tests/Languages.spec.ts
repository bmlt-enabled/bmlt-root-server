import { beforeAll, describe, expect, test } from 'vitest';
import { render, screen } from '@testing-library/svelte';
import '@testing-library/jest-dom';
import userEvent from '@testing-library/user-event';

import App from '../App.svelte';
import { setupMocks, sharedAfterEach } from './sharedDataAndMocks';
import { translations } from '../stores/localization';

beforeAll(async () => {
  setupMocks();
});

afterEach(async () => {
  await sharedAfterEach();
  // put the default language back to English in case a test changed it
  translations.setLanguage('en');
});

describe('language selection tests', () => {
  test('test language selection menu with a successful login', async () => {
    const user = userEvent.setup();
    render(App);
    const langs: HTMLSelectElement = await screen.findByRole('combobox', { name: 'Select Language' });
    expect(langs.length).toBe(4);
    expect(langs.item(0)?.label).toBe('Choose option ...');
    expect(langs.item(1)?.label).toBe('English');
    expect(langs.item(2)?.label).toBe('Deutsch');
    expect(langs.item(3)?.label).toBe('Français');
    await userEvent.selectOptions(langs, ['Deutsch']);
    // login screen should now be in German, including the Language Selection menu title
    expect(await screen.findByRole('combobox', { name: 'Sprache auswählen' })).toBeEnabled();
    await user.type(await screen.findByRole('textbox', { name: 'Benutzername' }), 'serveradmin');
    await user.type(await screen.findByLabelText('Passwort'), 'serveradmin-password');
    expect(await screen.findByRole('textbox', { name: 'Benutzername' })).toHaveDisplayValue('serveradmin');
    expect(await screen.findByLabelText('Passwort')).toHaveDisplayValue('serveradmin-password');
    await user.click(await screen.findByRole('button', { name: 'Anmelden' }));
    // after a successful login, we should see 'Willkommen Server Administrator' and the navbar
    expect(await screen.findByText('Willkommen Server Administrator')).toBeInTheDocument();
    // check for one element in the navbar
    // TODO: shouldn't need to say hidden!
    expect(await screen.findByRole('link', { name: 'Benutzer', hidden: true })).toBeEnabled();
    // Log out
    await user.click(await screen.findByRole('link', { name: 'Abmelden', hidden: true }));
    // Make sure we're back at the login screen (which should still be in German)
    expect(await screen.findByRole('button', { name: 'Anmelden' })).toBeEnabled();
    expect(await screen.findByRole('combobox', { name: 'Sprache auswählen' })).toBeEnabled();
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

  test('test ensure same keys exist for all languages and are sorted', () => {
    const allTranslations = translations.getTranslationsForAllLanguages();
    const languages = Object.keys(allTranslations);
    const referenceLanguage = 'en';
    const referenceKeys = Object.keys(allTranslations[referenceLanguage]).sort();

    languages.forEach((language) => {
      const currentKeys = Object.keys(allTranslations[language]);
      expect(currentKeys).toEqual(referenceKeys);
    });
  });
});
