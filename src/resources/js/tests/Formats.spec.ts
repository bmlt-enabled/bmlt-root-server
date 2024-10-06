import { beforeAll, beforeEach, describe, test } from 'vitest';
import { screen } from '@testing-library/svelte';
import '@testing-library/jest-dom';
import { login, mockDeletedFormatId, mockSavedFormatCreate, mockSavedFormatUpdate, sharedAfterEach, sharedBeforeAll, sharedBeforeEach } from './sharedDataAndMocks';
import userEvent from '@testing-library/user-event';

beforeAll(sharedBeforeAll);
beforeEach(sharedBeforeEach);
afterEach(sharedAfterEach);

// Unfortunately it's difficult to test the Accordion part of the popup Format Form because onMount doesn't work correctly
// for components when doing unit tests.  So this is currently not tested.
// See the longer comment in src/resources/js/components/AccountServiceBodyList.svelte

describe('check content in Formats tab', () => {
  test('check list of formats', async () => {
    await login('serveradmin', 'Formats');
    expect(await screen.findByRole('heading', { name: 'Formats', level: 2 })).toBeInTheDocument();
    expect(await screen.findByRole('textbox', { name: 'Search' })).toBeInTheDocument();
    // There should be 6 formats, with 2 cells per format (name and a delete icon)
    const cells = screen.getAllByRole('cell');
    expect(cells.length).toBe(12);
    // check for a couple of representative formats
    expect(screen.getByRole('cell', { name: 'Closed' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'Basic Text' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'Agnostisch (no English version available)' })).toBeInTheDocument();
  });

  test('edit a format', async () => {
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

  test('delete a format', async () => {
    const user = await login('serveradmin', 'Formats');
    await user.click(await screen.findByRole('button', { name: 'Delete Format Basic Text' }));
    await user.click(await screen.findByRole('checkbox', { name: "Yes, I'm sure." }));
    await user.click(await screen.findByRole('button', { name: 'Delete' }));
    expect(mockDeletedFormatId).toBe(19);
    expect(mockSavedFormatCreate).toBe(null);
    expect(mockSavedFormatUpdate).toBe(null);
  });

  // Alas can't do much testing with create format, due to the problem with the Accordion (see comment at top).
  // This at least tests for the error condition of trying to create a format with no translations.
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
