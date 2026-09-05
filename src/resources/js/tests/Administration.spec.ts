import { beforeAll, beforeEach, describe, test, type MockInstance } from 'vitest';
import { screen } from '@testing-library/svelte';
import '@testing-library/jest-dom';
import * as XLSX from 'xlsx';
import { translations } from '../stores/localization';
import { laravelLogMissing, login, loginDeutsch, sharedAfterEach, sharedBeforeAll, sharedBeforeEach } from './sharedDataAndMocks';

// These tests include the mocks that override the behavior of clicking on a link and of console.error.  We don't want to change this
// for the other tests, so they are here rather than in the shared mocks file.  We still import the shared mocks.
// We also import xlsx, which is big -- so we don't want to import that in the sharedDataAndMocks since other tests don't need it.

// Perhaps overkill ... but keep track of whether URL.createObjectURL and URL.revokeObjectURL are defined, and if they are,
// restore the old versions after the test is complete.  (They will be defined in a browser but not the testing environment.)
let originalCreateObjectURL: any, originalRevokeObjectURL: any;
let mockClickInfo: { download: string; href: string } | null;
function mockClick(this: HTMLAnchorElement) {
  mockClickInfo = { download: this.download, href: this.href };
}
let consoleErrorSpy: MockInstance<typeof console.error>;
let xlsxWriteFileSpy: MockInstance<typeof XLSX.writeFileXLSX>;
let translationsWB: XLSX.WorkBook | null;
let translationsFile: string;

vi.mock('xlsx', { spy: true });

beforeAll(() => {
  originalCreateObjectURL = URL.createObjectURL;
  originalRevokeObjectURL = URL.revokeObjectURL;
  HTMLAnchorElement.prototype.click = vi.fn().mockImplementation(mockClick);
  URL.createObjectURL = vi.fn().mockImplementation(() => 'blob:http://localhost:8000/dummyblob');
  URL.revokeObjectURL = vi.fn();
  sharedBeforeAll();
});
beforeEach(() => {
  mockClickInfo = null;
  translationsWB = null;
  translationsFile = '';
  consoleErrorSpy = vi.spyOn(console, 'error').mockImplementation(() => {});
  xlsxWriteFileSpy = vi.spyOn(XLSX, 'writeFileXLSX').mockImplementation((wb, file) => {
    translationsWB = wb;
    translationsFile = file;
  });
  sharedBeforeEach();
});
afterEach(() => {
  xlsxWriteFileSpy.mockReset();
  xlsxWriteFileSpy.mockRestore();
  consoleErrorSpy.mockRestore();
  // put the default language back to English (one of the tests changes it)
  translations.setLanguage('en');
  sharedAfterEach();
});
afterAll(() => {
  if (originalCreateObjectURL) {
    URL.createObjectURL = originalCreateObjectURL;
  }
  if (originalRevokeObjectURL) {
    URL.revokeObjectURL = originalRevokeObjectURL;
  }
});

describe('check Administration tab', () => {
  test('check NAWS import', async () => {
    await login('serveradmin', 'Administration');
    const fileInput = await screen.findByLabelText('Update World Committee Codes');
    expect(fileInput.nodeName).toBe('INPUT');
    expect(screen.queryByText('Supported formats: Excel (.xlsx) and CSV (.csv)')).toBeInTheDocument();
    // TODO: unfortunately I couldn't get the remaining steps in checking the NAWS import button to work, so am just leaving it with this minimal test for now
  });

  test('check download laravel log', async () => {
    laravelLogMissing.missing = false;
    const user = await login('serveradmin', 'Administration');
    const download = await screen.findByRole('button', { name: 'Download Laravel Log' });
    await user.click(download);
    expect(mockClickInfo?.download).toBe('laravel.log.gz');
    expect(mockClickInfo?.href).toBe('blob:http://localhost:8000/dummyblob');
    expect(consoleErrorSpy).toHaveBeenCalledTimes(0);
  });

  test('check bad laravel log', async () => {
    laravelLogMissing.missing = true;
    const user = await login('serveradmin', 'Administration');
    const download = await screen.findByRole('button', { name: 'Download Laravel Log' });
    await user.click(download);
    expect(mockClickInfo).toBe(null);
    expect(screen.getByText('No logs found')).toBeInTheDocument();
    expect(consoleErrorSpy).toHaveBeenCalledTimes(2);
    expect(consoleErrorSpy).toHaveBeenCalledWith('Failed to download Laravel log:', 'Response Error');
  });

  test('check download translations spreadsheet for English', async () => {
    laravelLogMissing.missing = false;
    const user = await login('serveradmin', 'Administration');
    const download = await screen.findByRole('button', { name: 'Download Translations Spreadsheet' });
    await user.click(download);
    await vi.waitFor(() => {
      expect(xlsxWriteFileSpy).toHaveBeenCalledTimes(1);
    });
    expect(consoleErrorSpy).toHaveBeenCalledTimes(0);
    expect(translationsFile).toBe('translations.xlsx');
    const trans = translationsWB?.Sheets.translations;
    expect(trans).not.toBe(undefined);
    if (trans) {
      // the default login language is English, so there should be only 2 columns in the spreadsheet
      expect(trans['A1'].v).toBe('Key');
      expect(trans['B1'].v).toBe('English');
      expect(trans['C1']).toBe(undefined);
      expect(trans['A2'].v).toBe('accountSettingsTitle');
      expect(trans['B2'].v).toBe('Account Settings');
      expect(trans['C2']).toBe(undefined);
    }
  });

  test('check download translations spreadsheet for German', async () => {
    laravelLogMissing.missing = false;
    const user = await loginDeutsch('serveradmin', 'Administration');
    const download = await screen.findByRole('button', { name: 'Übersetzungstabelle herunterladen' });
    await user.click(download);
    await vi.waitFor(() => {
      expect(xlsxWriteFileSpy).toHaveBeenCalledTimes(1);
    });
    expect(consoleErrorSpy).toHaveBeenCalledTimes(0);
    expect(translationsFile).toBe('translations.xlsx');
    const trans = translationsWB?.Sheets.translations;
    expect(trans).not.toBe(undefined);
    if (trans) {
      expect(trans['A1'].v).toBe('Key');
      expect(trans['B1'].v).toBe('English');
      expect(trans['C1'].v).toBe('Deutsch');
      expect(trans['A2'].v).toBe('accountSettingsTitle');
      expect(trans['B2'].v).toBe('Account Settings');
      expect(trans['C2'].v).toBe('Account Einstellungen');
    }
  });
});
