import { beforeAll, beforeEach, describe, expect, test, vi, type MockInstance } from 'vitest';
import { screen, waitFor } from '@testing-library/svelte';
import '@testing-library/jest-dom';
import userEvent from '@testing-library/user-event';
import * as XLSX from 'xlsx';

import { login, mockDeletedUserId, mockSavedUserCreate, mockSavedUserUpdate, sharedAfterEach, sharedBeforeAll, sharedBeforeEach } from './sharedDataAndMocks';

let xlsxWriteFileSpy: MockInstance<typeof XLSX.writeFileXLSX>;
let usersWB: XLSX.WorkBook | null;
let usersFile: string;

vi.mock('xlsx', { spy: true });

beforeAll(() => {
  sharedBeforeAll();
});

beforeEach(() => {
  usersWB = null;
  usersFile = '';
  xlsxWriteFileSpy = vi.spyOn(XLSX, 'writeFileXLSX').mockImplementation((wb, file) => {
    usersWB = wb;
    usersFile = file;
  });
  sharedBeforeEach();
});

afterEach(() => {
  xlsxWriteFileSpy.mockReset();
  xlsxWriteFileSpy.mockRestore();
  sharedAfterEach();
});

describe('check content in User tab when logged in as various users', () => {
  test('check layout when logged in as serveradmin', async () => {
    await login('serveradmin', 'Users');
    expect(await screen.findByRole('heading', { name: 'Users', level: 2 })).toBeInTheDocument();
    expect(await screen.findByRole('textbox', { name: 'Search' })).toBeInTheDocument();
    // There should be 10 users, with 2 cells per user (display name and a delete icon)
    const cells = screen.getAllByRole('cell');
    expect(cells.length).toBe(20);
    // check for a couple of representative users
    expect(screen.getByRole('cell', { name: 'Big Region' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'Small Observer' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'Small Deactivated' })).toBeInTheDocument();
  });

  test('check layout when logged in as Northern Zone', async () => {
    await login('NorthernZone', 'Users');
    // Wait for the Users page to load
    await waitFor(
      async () => {
        expect(screen.getByRole('heading', { name: 'Users', level: 2 })).toBeInTheDocument();
      },
      { timeout: 10000 }
    );
    // There should be 4 users, with 1 cell per user (display name but no delete icon)
    const cells = await screen.findAllByRole('cell', {}, { timeout: 10000 });
    expect(cells.length).toBe(4);
    expect(screen.getByRole('cell', { name: 'Big Region' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'Small Region' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'Small Observer' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'Small Deactivated' })).toBeInTheDocument();
    // Big Region Admin 2 is owned by the server admin, so shouldn't show up here
  }, 15000);

  test('check layout when logged in as Big Region', async () => {
    await login('BigRegion', 'Users');
    // Wait for the Users page to load
    await waitFor(
      async () => {
        expect(screen.getByRole('heading', { name: 'Users', level: 2 })).toBeInTheDocument();
      },
      { timeout: 10000 }
    );
    // There should be 4 users, with 1 cell per user (display name but no delete icon)
    const cells = await screen.findAllByRole('cell', {}, { timeout: 10000 });
    expect(cells.length).toBe(4);
    expect(screen.getByRole('cell', { name: 'Mountain Area' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'River City Area' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'Rural Area' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'Rural Area Admin 2' })).toBeInTheDocument();
  }, 15000);

  test('check layout when logged in as Small Region', async () => {
    await login('SmallRegion', 'Users');
    // Wait for the Users page to load
    await waitFor(
      async () => {
        expect(screen.getByRole('heading', { name: 'Users', level: 2 })).toBeInTheDocument();
      },
      { timeout: 10000 }
    );
    // Wait for the "no users" message
    await waitFor(
      () => {
        expect(screen.getByText('No other users found that this user can edit')).toBeInTheDocument();
        expect(screen.queryByText('Formats')).not.toBeInTheDocument();
      },
      { timeout: 10000 }
    );
  }, 15000);

  test('check layout when logged in as Small Observer', async () => {
    await login('SmallObserver', null); // Don't try to navigate to Home tab since it might not be visible
    await waitFor(
      () => {
        expect(screen.queryByText('Users')).not.toBeInTheDocument();
        expect(screen.queryByText('Service Bodies')).not.toBeInTheDocument();
      },
      { timeout: 10000 }
    );
  }, 15000);
});

describe('check editing, adding, and deleting users using the popup dialog boxes', () => {
  test('logged in as serveradmin; edit Big Region', async () => {
    // For each field displayed in the popup, check the default contents, edit the field, and check the result.
    // Then save the edits, and check the contents of the User Update request.
    const user = await login('serveradmin', 'Users');
    await user.click(await screen.findByRole('cell', { name: 'Big Region' }));
    // The applyChanges button should be disabled at this point since there haven't been any edits.
    // We'll need to find the Apply Changes button again later, after there have been changes -- for some reason it's
    // a different button after it's enabled.
    const b = screen.getByRole('button', { name: 'Apply Changes' });
    expect(b).toBeDisabled();
    const userType = screen.getByRole('combobox', { name: 'User Type' }) as HTMLSelectElement;
    expect(userType.value).toBe('serviceBodyAdmin');
    await userEvent.selectOptions(userType, ['Observer']);
    expect(userType.value).toBe('observer');
    const ownedBy = screen.getByRole('combobox', { name: 'Owned By' }) as HTMLSelectElement;
    expect(ownedBy.value).toBe('2'); // id of Northern Zone
    await userEvent.selectOptions(ownedBy, ['1']);
    expect(ownedBy.value).toBe('1');
    const displayName = screen.getByRole('textbox', { name: 'Name' }) as HTMLInputElement;
    expect(displayName.value).toBe('Big Region');
    await user.clear(displayName);
    await user.type(displayName, 'Bigger Region');
    expect(displayName.value).toBe('Bigger Region');
    const email = screen.getByRole('textbox', { name: 'Email' }) as HTMLInputElement;
    expect(email.value).toBe('big@bmlt.app');
    await user.clear(email);
    await user.type(email, 'bigger@bmlt.app');
    expect(email.value).toBe('bigger@bmlt.app');
    const description = screen.getByRole('textbox', { name: 'Description' }) as HTMLInputElement;
    expect(description.value).toBe('Big Region Administrator');
    // just for variety we don't clear description, just append to it
    await user.type(description, ' now bigger');
    expect(description.value).toBe('Big Region Administrator now bigger');
    const userName = screen.getByRole('textbox', { name: 'Username' }) as HTMLInputElement;
    expect(userName.value).toBe('BigRegion');
    await user.clear(userName);
    await user.type(userName, 'BiggerRegion');
    expect(userName.value).toBe('BiggerRegion');
    const password = screen.getByLabelText('Password') as HTMLInputElement;
    expect(password.value).toBe('');
    await user.type(password, 'new password');
    expect(password.value).toBe('new password');
    const applyChanges = screen.getByRole('button', { name: 'Apply Changes' });
    // no need to explicitly test that applyChanges is enabled, since clicking on it wouldn't work if it were disabled
    await user.click(applyChanges);
    // check all the fields in the mock User Update for their new values
    expect(mockSavedUserUpdate?.type).toBe('observer');
    expect(mockSavedUserUpdate?.ownerId).toBe(1);
    expect(mockSavedUserUpdate?.displayName).toBe('Bigger Region');
    expect(mockSavedUserUpdate?.email).toBe('bigger@bmlt.app');
    expect(mockSavedUserUpdate?.description).toBe('Big Region Administrator now bigger');
    expect(mockSavedUserUpdate?.username).toBe('BiggerRegion');
    expect(mockSavedUserUpdate?.password).toBe('new password');
    // check that user create and user delete weren't touched
    expect(mockSavedUserCreate).toBe(null);
    expect(mockDeletedUserId).toBe(null);
  });

  test('logged in as serveradmin; select Add User', async () => {
    const user = await login('serveradmin', 'Users');
    await user.click(await screen.findByRole('button', { name: 'Add User' }));
    // check that the User Type menu is there but don't change the default (we already tested changing it in the update user test)
    const userType = screen.getByRole('combobox', { name: 'User Type' }) as HTMLSelectElement;
    expect(userType.value).toBe('serviceBodyAdmin');
    const ownedBy = screen.getByRole('combobox', { name: 'Owned By' }) as HTMLSelectElement;
    expect(ownedBy.value).toBe('1'); // id of serveradmin
    await userEvent.selectOptions(ownedBy, ['2']);
    expect(ownedBy.value).toBe('2');
    const displayName = screen.getByRole('textbox', { name: 'Name' }) as HTMLInputElement;
    await user.type(displayName, 'Weird Region');
    expect(displayName.value).toBe('Weird Region');
    const email = screen.getByRole('textbox', { name: 'Email' }) as HTMLInputElement;
    await user.type(email, 'weird@bmlt.app');
    expect(email.value).toBe('weird@bmlt.app');
    const description = screen.getByRole('textbox', { name: 'Description' }) as HTMLInputElement;
    await user.type(description, 'a weird description');
    expect(description.value).toBe('a weird description');
    const userName = screen.getByRole('textbox', { name: 'Username' }) as HTMLInputElement;
    await user.type(userName, 'WeirdRegion');
    expect(userName.value).toBe('WeirdRegion');
    const password = screen.getByLabelText('Password') as HTMLInputElement;
    await user.type(password, 'weird password');
    expect(password.value).toBe('weird password');
    // at this point there are *two* 'Add User' buttons.  Click the second one.  (Kind of funky ...)
    const addButtons = screen.getAllByRole('button', { name: 'Add User' });
    await user.click(addButtons[1]);
    expect(mockSavedUserCreate?.type).toBe('serviceBodyAdmin');
    expect(mockSavedUserCreate?.ownerId).toBe(2);
    expect(mockSavedUserCreate?.displayName).toBe('Weird Region');
    expect(mockSavedUserCreate?.email).toBe('weird@bmlt.app');
    expect(mockSavedUserCreate?.description).toBe('a weird description');
    expect(mockSavedUserCreate?.username).toBe('WeirdRegion');
    expect(mockSavedUserCreate?.password).toBe('weird password');
    expect(mockSavedUserUpdate).toBe(null);
    expect(mockDeletedUserId).toBe(null);
  });

  test('logged in as serveradmin; select Add User, fill in bad data, and check for error messages', async () => {
    const user = await login('serveradmin', 'Users');
    await user.click(await screen.findByRole('button', { name: 'Add User' }));
    const password = screen.getByLabelText('Password') as HTMLInputElement;
    await user.type(password, 'short');
    const addButtons = screen.getAllByRole('button', { name: 'Add User' });
    await user.click(addButtons[1]);
    expect(screen.getByText('displayName is a required field')).toBeInTheDocument();
    expect(screen.getByText('username is a required field')).toBeInTheDocument();
    expect(screen.getByText('password must be between 12 and 255 characters')).toBeInTheDocument();
  });

  test('logged in as Northern Zone; edit Big Region', async () => {
    // We already tested the editing form when logged in as serveradmin.  Here just test that the User Type
    // and Owned By menus are disabled and also hidden, and that one field (Name) is present and enabled.
    const user = await login('NorthernZone', 'Users');
    await user.click(await screen.findByRole('cell', { name: 'Big Region' }));
    expect(screen.getByRole('combobox', { name: 'User Type', hidden: true })).toBeDisabled();
    expect(screen.getByRole('combobox', { name: 'Owned By', hidden: true })).toBeDisabled();
    expect(screen.getByRole('textbox', { name: 'Name' })).toBeEnabled();
  });

  test('logged in as serveradmin; delete Small Region', async () => {
    const user = await login('serveradmin', 'Users');
    await user.click(await screen.findByRole('button', { name: 'Delete User Small Region' }));
    // For some reason with the svelte 5 version of flowbite-svelte, the following query stopped working -- I have not been able to find
    // the confirm checkbox using name, description, etc.  However, there is only one checkbox at this point, so I'm hacking around the
    // problem by just finding the checkbox without using its name.
    // TODO: use some better query if it starts working again
    // await user.click(await screen.findByRole('checkbox', { name: "Yes, I'm sure." }));
    await user.click(await screen.findByRole('checkbox'));
    await user.click(await screen.findByRole('button', { name: 'Delete' }));
    expect(mockDeletedUserId).toBe(4);
    expect(mockSavedUserCreate).toBe(null);
    expect(mockSavedUserUpdate).toBe(null);
  });

  test('logged in as serveradmin; try to delete Big Region', async () => {
    // this should fail because Big Region has children
    const user = await login('serveradmin', 'Users');
    await user.click(await screen.findByRole('button', { name: 'Delete User Big Region' }));
    // TODO: see comment on previous test about finding the checkbox
    // await user.click(await screen.findByRole('checkbox', { name: "Yes, I'm sure." }));
    await user.click(await screen.findByRole('checkbox'));
    await user.click(await screen.findByRole('button', { name: 'Delete' }));
    expect(screen.getByText(/Error: The user could not be deleted/)).toBeInTheDocument();
    expect(mockDeletedUserId).toBe(null);
    expect(mockSavedUserCreate).toBe(null);
    expect(mockSavedUserUpdate).toBe(null);
  });

  test('logged in as serveradmin; try to delete Small Region Observer', async () => {
    // this should fail because Small Observer is observing the Northern Zone
    const user = await login('serveradmin', 'Users');
    await user.click(await screen.findByRole('button', { name: 'Delete User Small Observer' }));
    // TODO: see comment on previous test about finding the checkbox
    // await user.click(await screen.findByRole('checkbox', { name: "Yes, I'm sure." }));
    await user.click(await screen.findByRole('checkbox'));
    await user.click(await screen.findByRole('button', { name: 'Delete' }));
    expect(screen.getByText(/Error: The user could not be deleted/)).toBeInTheDocument();
    expect(mockDeletedUserId).toBe(null);
    expect(mockSavedUserCreate).toBe(null);
    expect(mockSavedUserUpdate).toBe(null);
  });

  test('confirm modal appears when attempting to click outside modal with unsaved changes', async () => {
    const user = await login('serveradmin', 'Users');
    await user.click(await screen.findByRole('cell', { name: 'Big Region' }));
    const description = screen.getByRole('textbox', { name: 'Description' }) as HTMLInputElement;
    await user.clear(description);
    await user.type(description, 'Bigger Region');
    const outsideElement = document.body;
    await user.click(outsideElement);
    expect(screen.getByText('You have unsaved changes. Do you really want to close?')).toBeInTheDocument();
  });

  test('test Confirm modal appears when attempting to close with unsaved changes', async () => {
    const user = await login('serveradmin', 'Users');
    await user.click(await screen.findByRole('cell', { name: 'Big Region' }));
    const description = screen.getByRole('textbox', { name: 'Description' }) as HTMLInputElement;
    await user.clear(description);
    await user.type(description, 'Bigger Region');
    await user.click(await screen.findByRole('button', { name: 'Close' }));
    expect(screen.getByText('You have unsaved changes. Do you really want to close?')).toBeInTheDocument();
  });
});

describe('Spreadsheet download functionality', () => {
  test('Download Spreadsheet button is present when logged in as serveradmin', async () => {
    await login('serveradmin', 'Users');
    const downloadButton = await screen.findByRole('button', { name: /Download Spreadsheet/i });
    expect(downloadButton).toBeInTheDocument();
    expect(downloadButton).toBeEnabled();
  });

  test('Download Spreadsheet button triggers download when clicked', async () => {
    const user = await login('serveradmin', 'Users');
    const downloadButton = await screen.findByRole('button', { name: /Download Spreadsheet/i });
    await user.click(downloadButton);

    // Wait for download to complete
    await waitFor(() => {
      expect(xlsxWriteFileSpy).toHaveBeenCalled();
    });

    const filename = usersFile;
    expect(filename).toMatch(/^users_\d{4}_\d{2}_\d{2}_\d{2}_\d{2}_\d{2}\.xlsx$/);
  });

  test('Download Spreadsheet contains correct user data', async () => {
    const user = await login('serveradmin', 'Users');
    const downloadButton = await screen.findByRole('button', { name: /Download Spreadsheet/i });
    await user.click(downloadButton);

    await waitFor(() => {
      expect(xlsxWriteFileSpy).toHaveBeenCalled();
    });

    // Check workbook structure and content
    expect(usersWB).not.toBe(null);
    const sheet = usersWB?.Sheets.Sheet1;
    expect(sheet).not.toBe(undefined);

    if (sheet) {
      // Check headers
      expect(sheet['A1'].v).toBe('id');
      expect(sheet['B1'].v).toBe('username');
      expect(sheet['C1'].v).toBe('displayName');
      expect(sheet['D1'].v).toBe('email');
      expect(sheet['E1'].v).toBe('type');
      expect(sheet['F1'].v).toBe('ownerId');
      expect(sheet['G1'].v).toBe('lastLoginAt');

      // Check for some known user data (from shared mocks)
      // Big Region user should be in the spreadsheet
      // Note: exact row depends on sort order, so we'll check that the data exists
      const range = XLSX.utils.decode_range(sheet['!ref'] || 'A1');
      let foundBigRegion = false;
      let foundSmallObserver = false;

      // Check rows (starting from row 2, since row 1 is headers)
      for (let row = 2; row <= range.e.r + 1; row++) {
        const displayName = sheet[`C${row}`]?.v;
        const email = sheet[`D${row}`]?.v;
        const type = sheet[`E${row}`]?.v;

        if (displayName === 'Big Region' && email === 'big@bmlt.app') {
          foundBigRegion = true;
          expect(sheet[`B${row}`].v).toBe('BigRegion'); // username
          expect(type).toBe('serviceBodyAdmin');
        }

        if (displayName === 'Small Observer' && email === 'smallobserver@bmlt.app') {
          foundSmallObserver = true;
          expect(sheet[`B${row}`].v).toBe('SmallObserver'); // username
          expect(type).toBe('observer');
        }
      }

      expect(foundBigRegion).toBe(true);
      expect(foundSmallObserver).toBe(true);
    }
  });

  test('Download Spreadsheet button is not present when logged in as non-admin', async () => {
    await login('NorthernZone', 'Users');
    await waitFor(
      async () => {
        expect(screen.getByRole('heading', { name: 'Users', level: 2 })).toBeInTheDocument();
      },
      { timeout: 10000 }
    );
    const downloadButton = screen.queryByRole('button', { name: /Download Spreadsheet/i });
    expect(downloadButton).not.toBeInTheDocument();
  }, 15000);

  test('displays last login and user ID for existing user', async () => {
    const user = await login('serveradmin', 'Users');
    await user.click(await screen.findByRole('cell', { name: 'Big Region' }));

    await waitFor(() => {
      expect(screen.getByText('Last Login:')).toBeInTheDocument();
      expect(screen.getByText('User ID:')).toBeInTheDocument();
    });
  });

  test('user ID is displayed on the right side', async () => {
    const user = await login('serveradmin', 'Users');
    await user.click(await screen.findByRole('cell', { name: 'Big Region' }));

    await waitFor(() => {
      const userIdElement = screen.getByText('User ID:').closest('div');
      expect(userIdElement).toHaveClass('ml-auto');
    });
  });

  test('last login and user ID are not displayed for new user', async () => {
    const user = await login('serveradmin', 'Users');
    await user.click(await screen.findByRole('button', { name: 'Add User' }));

    expect(screen.queryByText('Last Login:')).not.toBeInTheDocument();
    expect(screen.queryByText('User ID:')).not.toBeInTheDocument();
  });

  test('user ID is displayed even without last login', async () => {
    const user = await login('serveradmin', 'Users');
    // Small Region doesn't have lastLoginAt
    await user.click(await screen.findByRole('cell', { name: 'Small Region' }));

    await waitFor(() => {
      expect(screen.queryByText('Last Login:')).not.toBeInTheDocument();
      expect(screen.getByText('User ID:')).toBeInTheDocument();
    });
  });
});
