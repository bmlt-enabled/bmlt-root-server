import { beforeAll, beforeEach, describe, test } from 'vitest';
import { screen } from '@testing-library/svelte';
import '@testing-library/jest-dom';
import { login, loginDeutsch, mockDeletedFormatId, mockSavedFormatCreate, mockSavedFormatUpdate, sharedAfterEach, sharedBeforeAll, sharedBeforeEach } from './sharedDataAndMocks';
import userEvent from '@testing-library/user-event';
import { translations } from '../stores/localization';

beforeAll(sharedBeforeAll);
beforeEach(sharedBeforeEach);

afterEach(async () => {
  await sharedAfterEach();
  // put the default language back to English (one of the tests changes it)
  translations.setLanguage('en');
});

describe('check content in Formats tab', () => {
  test('check list of formats', async () => {
    await login('serveradmin', 'Formats');
    expect(await screen.findByRole('heading', { name: 'Formats', level: 2 })).toBeInTheDocument();
    expect(await screen.findByRole('textbox', { name: 'Search' })).toBeInTheDocument();
    // There should be 8 formats, with 2 cells per format (name and a delete icon)
    const cells = screen.getAllByRole('cell');
    expect(cells.length).toBe(16);
    // check for a couple of representative formats
    expect(screen.getByRole('cell', { name: 'Closed' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'Basic Text' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'Agnostisch (no English version available)' })).toBeInTheDocument();
  });

  test('edit a format', async () => {
    // this test doesn't involve editing a translation in the popup -- that's tested separately to keep the test size more manageable
    const user = await login('serveradmin', 'Formats');
    await user.click(await screen.findByRole('cell', { name: 'Basic Text' }));
    const b = screen.getByRole('button', { name: 'Apply Changes' });
    expect(b).toBeDisabled();
    const nawsFormat = screen.getByRole('combobox', { name: 'NAWS Format' }) as HTMLSelectElement;
    expect(nawsFormat.value).toBe('BT');
    // pretty silly to change it to Candlelight, but it's just for the sake of unit testing ...
    await userEvent.selectOptions(nawsFormat, ['CAN']);
    expect(nawsFormat.value).toBe('CAN');
    const formatType = screen.getByRole('combobox', { name: 'Format Type' }) as HTMLSelectElement;
    expect(formatType.value).toBe('MEETING_FORMAT');
    await userEvent.selectOptions(formatType, ['COMMON_NEEDS_OR_RESTRICTION']);
    expect(formatType.value).toBe('COMMON_NEEDS_OR_RESTRICTION');
    const applyChanges = screen.getByRole('button', { name: 'Apply Changes' });
    await user.click(applyChanges);
    expect(mockSavedFormatUpdate?.worldId).toBe('CAN');
    expect(mockSavedFormatUpdate?.type).toBe('COMMON_NEEDS_OR_RESTRICTION');
    expect(mockSavedFormatCreate).toBe(null);
    expect(mockDeletedFormatId).toBe(null);
  });

  test('edit translations', async () => {
    const user = await login('serveradmin', 'Formats');
    await user.click(await screen.findByRole('cell', { name: 'Basic Text' }));
    // Since English is the current default language, that translation in the accordion should be open and the others closed.
    // However, BasicAccordion just hides them with css, so they are still in the DOM and can be edited.
    const toggle_de = await screen.findByRole('button', { name: /toggle accordion de/i });
    const toggle_en = await screen.findByRole('button', { name: /toggle accordion en/i });
    const toggle_fr = await screen.findByRole('button', { name: /toggle accordion fr/i });
    expect(toggle_de.ariaExpanded).toBe('false');
    expect(toggle_en.ariaExpanded).toBe('true');
    expect(toggle_fr.ariaExpanded).toBe('false');
    const en_key = (await screen.findByRole('textbox', { name: 'en key' })) as HTMLInputElement;
    const de_key = (await screen.findByRole('textbox', { name: 'de key' })) as HTMLInputElement;
    expect(en_key.value).toBe('BT');
    await user.clear(en_key);
    await user.type(en_key, 'BT1');
    expect(de_key.value).toBe('BT');
    await user.clear(de_key);
    await user.type(de_key, 'BT2');
    const de_description = (await screen.findByRole('textbox', { name: 'de description' })) as HTMLInputElement;
    expect(de_description.value).toBe('Lesen aus dem Basic Text');
    await user.clear(de_description);
    await user.type(de_description, 'Mehr lesen');
    const applyChanges = screen.getByRole('button', { name: 'Apply Changes' });
    await user.click(applyChanges);
    const new_en = mockSavedFormatUpdate?.translations.find((t) => t.language === 'en');
    const new_de = mockSavedFormatUpdate?.translations.find((t) => t.language === 'de');
    expect(new_en?.key).toBe('BT1');
    expect(new_de?.key).toBe('BT2');
    expect(new_de?.description).toBe('Mehr lesen');
    expect(mockSavedFormatCreate).toBe(null);
    expect(mockDeletedFormatId).toBe(null);
  });

  test('check accordion when German is selected', async () => {
    const user = await loginDeutsch('serveradmin', 'Formate');
    await user.click(await screen.findByRole('cell', { name: 'BasicText' }));
    const toggle_de = await screen.findByRole('button', { name: /toggle accordion de/i });
    const toggle_en = await screen.findByRole('button', { name: /toggle accordion en/i });
    const toggle_fr = await screen.findByRole('button', { name: /toggle accordion fr/i });
    expect(toggle_de.ariaExpanded).toBe('true');
    expect(toggle_en.ariaExpanded).toBe('false');
    expect(toggle_fr.ariaExpanded).toBe('false');
  });

  test('delete a format', async () => {
    const user = await login('serveradmin', 'Formats');
    await user.click(await screen.findByRole('button', { name: 'Delete Format Beginners' }));
    await user.click(await screen.findByRole('checkbox', { name: "Yes, I'm sure." }));
    await user.click(await screen.findByRole('button', { name: 'Delete' }));
    expect(mockDeletedFormatId).toBe(25);
    expect(mockSavedFormatCreate).toBe(null);
    expect(mockSavedFormatUpdate).toBe(null);
  });

  test('try to delete a format that is in use', async () => {
    const user = await login('serveradmin', 'Formats');
    await user.click(await screen.findByRole('button', { name: 'Delete Format Basic Text' }));
    await user.click(await screen.findByRole('checkbox', { name: "Yes, I'm sure." }));
    await user.click(await screen.findByRole('button', { name: 'Delete' }));
    expect(screen.getByText(/Error: The format could not be deleted because it is still associated with meetings./)).toBeInTheDocument();
    expect(mockDeletedFormatId).toBe(null);
    expect(mockSavedFormatCreate).toBe(null);
    expect(mockSavedFormatUpdate).toBe(null);
  });

  test('delete should be disabled for reserved formats', async () => {
    await login('serveradmin', 'Formats');
    const d = await screen.findByRole('button', { name: 'Delete Format Virtual Meeting' });
    expect(d).toBeDisabled();
  });

  test('create a new format', async () => {
    const user = await login('serveradmin', 'Formats');
    await user.click(await screen.findByRole('button', { name: 'Add Format' }));
    const en_key = (await screen.findByRole('textbox', { name: 'en key' })) as HTMLInputElement;
    const en_name = (await screen.findByRole('textbox', { name: 'en name' })) as HTMLInputElement;
    const en_description = (await screen.findByRole('textbox', { name: 'en description' })) as HTMLInputElement;
    await user.type(en_key, 'Zzz');
    await user.type(en_name, 'Sleepers');
    await user.type(en_description, 'Intended for people who sleep through meetings');
    const addButtons = await screen.findAllByRole('button', { name: 'Add Format' });
    // there are two 'Add Format' buttons at this point -- kind of a hack -- just pick the second one
    await user.click(addButtons[1]);
    const new_en = mockSavedFormatCreate?.translations.find((t) => t.language === 'en');
    expect(new_en?.key).toBe('Zzz');
    expect(new_en?.name).toBe('Sleepers');
    expect(new_en?.description).toBe('Intended for people who sleep through meetings');
    expect(mockSavedFormatUpdate).toBe(null);
    expect(mockDeletedFormatId).toBe(null);
  });

  test('try to create a format with no translations', async () => {
    const user = await login('serveradmin', 'Formats');
    await user.click(await screen.findByRole('button', { name: 'Add Format' }));
    const nawsFormat = screen.getByRole('combobox', { name: 'NAWS Format' }) as HTMLSelectElement;
    await userEvent.selectOptions(nawsFormat, ['CAN']);
    const addButtons = await screen.findAllByRole('button', { name: 'Add Format' });
    // there are two 'Add Format' buttons at this point -- kind of a hack -- just pick the second one
    await user.click(addButtons[1]);
    expect(await screen.findByText(/At least one translation is required/i)).toBeInTheDocument();
    expect(mockSavedFormatCreate).toBe(null);
    expect(mockSavedFormatUpdate).toBe(null);
    expect(mockDeletedFormatId).toBe(null);
  });
});
