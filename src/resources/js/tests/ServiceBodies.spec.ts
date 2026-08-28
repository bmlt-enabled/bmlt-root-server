import { beforeAll, beforeEach, describe, expect, test, vi, type MockInstance } from 'vitest';
import { screen, waitFor, within } from '@testing-library/svelte';
import '@testing-library/jest-dom';
import { login, mockDeletedServiceBodyId, mockSavedServiceBodyCreate, mockSavedServiceBodyUpdate, sharedAfterEach, sharedBeforeAll, sharedBeforeEach } from './sharedDataAndMocks';
import userEvent from '@testing-library/user-event';
import * as XLSX from 'xlsx';

let xlsxWriteFileSpy: MockInstance<typeof XLSX.writeFileXLSX>;
let serviceBodiesWB: XLSX.WorkBook | null;
let serviceBodiesFile: string;

vi.mock('xlsx', { spy: true });

beforeAll(() => {
  sharedBeforeAll();
});

beforeEach(() => {
  serviceBodiesWB = null;
  serviceBodiesFile = '';
  xlsxWriteFileSpy = vi.spyOn(XLSX, 'writeFileXLSX').mockImplementation((wb, file) => {
    serviceBodiesWB = wb;
    serviceBodiesFile = file;
  });
  sharedBeforeEach();
});

afterEach(() => {
  xlsxWriteFileSpy.mockReset();
  xlsxWriteFileSpy.mockRestore();
  sharedAfterEach();
});

describe('check content in Service Body tab when logged in as various users', () => {
  test('check layout when logged in as serveradmin', async () => {
    await login('serveradmin', 'Service Bodies');
    expect(await screen.findByRole('heading', { name: 'Service Bodies', level: 2 })).toBeInTheDocument();
    expect(await screen.findByRole('textbox', { name: 'Search' })).toBeInTheDocument();
    // There should be 6 service bodies, with 2 cells per user (name and a delete icon)
    const cells = screen.getAllByRole('cell');
    expect(cells.length).toBe(12);
    // check for a couple of representative service bodies
    expect(screen.getByRole('cell', { name: 'Big Region' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'Rural Area' })).toBeInTheDocument();
  });

  test('check layout when logged in as Northern Zone', async () => {
    await login('NorthernZone', 'Service Bodies');
    // There should be 6 service bodies, with 1 cell per service body (display name but no delete icon)
    const cells = await screen.findAllByRole('cell');
    expect(cells.length).toBe(6);
    expect(screen.getByRole('cell', { name: 'Northern Zone' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'Big Region' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'Mountain Area' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'River City Area' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'Rural Area' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'Small Region' })).toBeInTheDocument();
  });

  test('check layout when logged in as Big Region', async () => {
    login('BigRegion', 'Service Bodies');
    // There should be 4 service bodies, with 1 cell per service body (display name but no delete icon)
    const cells = await screen.findAllByRole('cell');
    expect(cells.length).toBe(4);
    expect(screen.getByRole('cell', { name: 'Big Region' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'Mountain Area' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'River City Area' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'Rural Area' })).toBeInTheDocument();
  });
});

describe('check editing, adding, and deleting service bodies using the popup dialog boxes', () => {
  test('logged in as serveradmin; edit Rural Area Service Body', async () => {
    // For each field displayed in the popup, check the default contents, edit the field, and check the result.
    // Then save the edits, and check the contents of the User Update request.
    const user = await login('serveradmin', 'Service Bodies');
    await user.click(await screen.findByRole('cell', { name: 'Rural Area' }));
    // The applyChanges button should be disabled at this point since there haven't been any edits.
    // We'll need to find the Apply Changes button again later, after there have been changes -- for some reason it's
    // a different button after it's enabled.
    const b = screen.getByRole('button', { name: 'Apply Changes' });
    expect(b).toBeDisabled();
    const name = screen.getByRole('textbox', { name: 'Name' }) as HTMLInputElement;
    expect(name.value).toBe('Rural Area');
    await user.clear(name);
    await user.type(name, 'More Rural Area');
    expect(name.value).toBe('More Rural Area');
    const serviceBodyAdmin = screen.getByRole('combobox', { name: 'Admin' }) as HTMLSelectElement;
    expect(serviceBodyAdmin.value).toBe('7'); // id of Rural Area Service Body
    await userEvent.selectOptions(serviceBodyAdmin, ['Mountain Area']);
    expect(serviceBodyAdmin.value).toBe('6'); // id of Mountain Area User
    const serviceBodiesType = screen.getByRole('combobox', { name: 'Service Body Type' }) as HTMLSelectElement;
    expect(serviceBodiesType.value).toBe('AS');
    await userEvent.selectOptions(serviceBodiesType, ['RS']);
    expect(serviceBodiesType.value).toBe('RS');
    const serviceBodyParent = screen.getByRole('combobox', { name: 'Service Body Parent' }) as HTMLSelectElement;
    expect(serviceBodyParent.value).toBe('102'); // id of Big Region
    await userEvent.selectOptions(serviceBodyParent, ['101']);
    expect(serviceBodyParent.value).toBe('101');

    const hiddenSelect = document.querySelector('select[name="assignedUserIds"]') as HTMLSelectElement;
    const initialSelectedOptions = Array.from(hiddenSelect.selectedOptions).map((option) => option.value);
    expect(initialSelectedOptions).toEqual(['11']);

    // Set the values directly on the hidden select (more reliable approach)
    const optionsToSelect = ['10', '6', '11']; // Big Region Admin 2, Mountain Area, Rural Area Admin 2

    // Clear existing selections
    Array.from(hiddenSelect.options).forEach((option) => (option.selected = false));

    // Select the desired options
    optionsToSelect.forEach((value) => {
      const option = Array.from(hiddenSelect.options).find((opt) => opt.value === value);
      if (option) option.selected = true;
    });

    // Trigger a change event
    hiddenSelect.dispatchEvent(new Event('change', { bubbles: true }));

    // Verify the selection
    const selectedOptions = Array.from(hiddenSelect.selectedOptions).map((option) => option.value);
    expect(selectedOptions).toEqual(expect.arrayContaining(['10', '6', '11']));

    const email = screen.getByRole('textbox', { name: 'Email' }) as HTMLInputElement;
    expect(email.value).toBe('rural@bmlt.app');
    await user.clear(email);
    await user.type(email, 'morerural@bmlt.app');
    expect(email.value).toBe('morerural@bmlt.app');
    const description = screen.getByRole('textbox', { name: 'Description' }) as HTMLInputElement;
    expect(description.value).toBe('Rural Area Description');
    await user.type(description, ' now more rural');
    expect(description.value).toBe('Rural Area Description now more rural');
    const url = screen.getByRole('textbox', { name: 'Web Site URL' }) as HTMLInputElement;
    expect(url.value).toBe('https://ruralarea.example.com');
    await user.clear(url);
    await user.type(url, 'https://moreruralarea.example.com');
    expect(url.value).toBe('https://moreruralarea.example.com');
    const helpline = screen.getByRole('textbox', { name: 'Helpline' }) as HTMLInputElement;
    expect(helpline.value).toBe('803-555-7247');
    await user.clear(helpline);
    await user.type(helpline, '843-555-7247');
    expect(helpline.value).toBe('843-555-7247');
    const worldid = screen.getByRole('textbox', { name: 'World Committee Code' }) as HTMLInputElement;
    expect(worldid.value).toBe('AS778');
    await user.clear(worldid);
    await user.type(worldid, 'AS788');
    expect(worldid.value).toBe('AS788');
    const applyChanges = screen.getByRole('button', { name: 'Apply Changes' });
    // no need to explicitly test that applyChanges is enabled, since clicking on it wouldn't work if it were disabled
    await user.click(applyChanges);
    // // check all the fields in the mock Service Body Update for their new values
    expect(mockSavedServiceBodyUpdate?.name).toBe('More Rural Area');
    expect(mockSavedServiceBodyUpdate?.adminUserId).toBe(6);
    expect(mockSavedServiceBodyUpdate?.type).toBe('RS');
    expect(mockSavedServiceBodyUpdate?.parentId).toBe(101);
    expect(mockSavedServiceBodyUpdate?.assignedUserIds).toEqual(expect.arrayContaining([10, 6, 11]));
    expect(mockSavedServiceBodyUpdate?.email).toBe('morerural@bmlt.app');
    expect(mockSavedServiceBodyUpdate?.description).toBe('Rural Area Description now more rural');
    expect(mockSavedServiceBodyUpdate?.url).toBe('https://moreruralarea.example.com');
    expect(mockSavedServiceBodyUpdate?.helpline).toBe('843-555-7247');
    expect(mockSavedServiceBodyUpdate?.worldId).toBe('AS788');
    // check that service body create and service body delete weren't touched
    expect(mockSavedServiceBodyCreate).toBe(null);
    expect(mockDeletedServiceBodyId).toBe(null);
  });

  test('logged in as serveradmin; select Add Service Body', async () => {
    const user = await login('serveradmin', 'Service Bodies');
    await user.click(await screen.findByRole('button', { name: 'Add Service Body' }));
    // The ServiceBodyForm should now be displayed.  So now there are *two* 'Add Service Body' buttons. Grab the second one.  It should be
    // initially disabled since no changes have been made to the form.
    const actuallyAddButton = screen.getAllByRole('button', { name: 'Add Service Body' })[1];
    expect(actuallyAddButton).toBeDisabled();
    // check that the User Type menu is there but don't change the default (we already tested changing it in the update user test)
    const serviceBodyType = screen.getByRole('combobox', { name: 'Service Body Type' }) as HTMLSelectElement;
    expect(serviceBodyType.value).toBe('AS'); // default area service
    const serviceBodyParent = screen.getByRole('combobox', { name: 'Service Body Parent' }) as HTMLSelectElement;
    expect(serviceBodyParent.value).toBe('-1'); // no parent
    const serviceBodyAdmin = screen.getByRole('combobox', { name: 'Admin' }) as HTMLSelectElement;
    await userEvent.selectOptions(serviceBodyAdmin, ['Mountain Area']);
    expect(serviceBodyAdmin.value).toBe('6'); // id of Mountain Area User
    const serviceBodiesType = screen.getByRole('combobox', { name: 'Service Body Type' }) as HTMLSelectElement;
    await userEvent.selectOptions(serviceBodiesType, ['RS']);
    expect(serviceBodiesType.value).toBe('RS');
    await userEvent.selectOptions(serviceBodyParent, ['101']);
    expect(serviceBodyParent.value).toBe('101');
    const hiddenSelect = document.querySelector('select[name="assignedUserIds"]') as HTMLSelectElement;
    const initialSelectedOptions = Array.from(hiddenSelect.selectedOptions).map((option) => option.value);
    expect(initialSelectedOptions).toEqual([]);
    const optionsToSelect = ['10', '6']; // Big Region Admin 2 (ID 10), Mountain Area (ID 6)
    optionsToSelect.forEach((value) => {
      const option = Array.from(hiddenSelect.options).find((opt) => opt.value === value);
      if (option) option.selected = true;
    });
    hiddenSelect.dispatchEvent(new Event('change', { bubbles: true }));
    const selectedOptions = Array.from(hiddenSelect.selectedOptions).map((option) => option.value);
    expect(selectedOptions).toEqual(expect.arrayContaining(['10', '6']));
    const name = screen.getByRole('textbox', { name: 'Name' }) as HTMLInputElement;
    await user.type(name, 'More Rural Area');
    expect(name.value).toBe('More Rural Area');
    const email = screen.getByRole('textbox', { name: 'Email' }) as HTMLInputElement;
    await user.type(email, 'morerural@bmlt.app');
    expect(email.value).toBe('morerural@bmlt.app');
    const description = screen.getByRole('textbox', { name: 'Description' }) as HTMLInputElement;
    await user.type(description, 'Rural Area Description');
    expect(description.value).toBe('Rural Area Description');
    const url = screen.getByRole('textbox', { name: 'Web Site URL' }) as HTMLInputElement;
    await user.type(url, 'https://moreruralarea.example.com');
    expect(url.value).toBe('https://moreruralarea.example.com');
    const helpline = screen.getByRole('textbox', { name: 'Helpline' }) as HTMLInputElement;
    await user.type(helpline, '843-555-7247');
    expect(helpline.value).toBe('843-555-7247');
    const worldid = screen.getByRole('textbox', { name: 'World Committee Code' }) as HTMLInputElement;
    await user.type(worldid, 'AS788');
    expect(worldid.value).toBe('AS788');
    expect(actuallyAddButton).toBeEnabled();
    await user.click(actuallyAddButton);
    expect(mockSavedServiceBodyCreate?.adminUserId).toBe(6);
    expect(mockSavedServiceBodyCreate?.type).toBe('RS');
    expect(mockSavedServiceBodyCreate?.parentId).toBe(101);
    expect(mockSavedServiceBodyCreate?.assignedUserIds).toEqual(expect.arrayContaining([10, 6]));
    expect(mockSavedServiceBodyCreate?.name).toBe('More Rural Area');
    expect(mockSavedServiceBodyCreate?.email).toBe('morerural@bmlt.app');
    expect(mockSavedServiceBodyCreate?.description).toBe('Rural Area Description');
    expect(mockSavedServiceBodyCreate?.url).toBe('https://moreruralarea.example.com');
    expect(mockSavedServiceBodyCreate?.helpline).toBe('843-555-7247');
    expect(mockSavedServiceBodyCreate?.worldId).toBe('AS788');
    expect(mockSavedServiceBodyUpdate).toBe(null);
    expect(mockDeletedServiceBodyId).toBe(null);
  });

  test('logged in as serveradmin; select Add Service body, fill in bad data, and check for error messages', async () => {
    const user = await login('serveradmin', 'Service Bodies');
    await user.click(await screen.findByRole('button', { name: 'Add Service Body' }));
    const email = screen.getByLabelText('Email') as HTMLInputElement;
    await user.type(email, 'blah');
    const addButtons = screen.getAllByRole('button', { name: 'Add Service Body' });
    await user.click(addButtons[1]);
    expect(screen.getByText('name is a required field')).toBeInTheDocument();
    expect(screen.getByText('email must be a valid email')).toBeInTheDocument();
  });

  test('logged in as Northern Zone; edit Big Region Service Body', async () => {
    // We already tested the editing form when logged in as serveradmin.  Here just test that the Name
    // field and the Admin selector menu are disabled, and that the Email field is present and enabled.
    const user = await login('NorthernZone', 'Service Bodies');
    await user.click(await screen.findByRole('cell', { name: 'Big Region' }));
    expect(screen.getByRole('textbox', { name: 'Name' })).toBeDisabled();
    expect(screen.getByRole('combobox', { name: 'Admin' })).toBeDisabled();
    expect(screen.getByRole('textbox', { name: 'Email' })).toBeEnabled();
  });

  test('logged in as Big Region; edit Mountain Area Service Body', async () => {
    // Check that issue 1488 is fixed ("Preserve hidden service body editors when non-admin updates a service body").
    // Mountain Area Service Body has Northern Zone Admin listed explicitly as a meeting list editor.  Northern Zone Admin shouldn't
    // be in the list of meeting editors when we are logged in as Big Region, but it shouldn't get erased if we make an edit.
    const user = await login('BigRegion', 'Service Bodies');
    await user.click(await screen.findByRole('cell', { name: 'Mountain Area' }));
    // The applyChanges button should be disabled at this point since there haven't been any edits.
    // (Before the issue was fixed, the UI thought the form was dirty on startup and applyChanges was enabled.)
    const b = screen.getByRole('button', { name: 'Apply Changes' });
    expect(b).toBeDisabled();
    const hiddenSelect = document.querySelector('select[name="assignedUserIds"]') as HTMLSelectElement;
    const initialSelectedOptions = Array.from(hiddenSelect.selectedOptions).map((option) => option.value);
    expect(initialSelectedOptions).toEqual([]); // Northern Zone Admin shouldn't be visible
    // The "Other Meeting Editors" section should list Northern Zone (the hidden editor) by display name
    // so the service body admin is aware they exist even though they can't be removed.
    const otherEditorsLabel = await screen.findByText('Other Meeting Editors');
    const otherEditorsBadges = otherEditorsLabel.parentElement as HTMLElement;
    expect(within(otherEditorsBadges).getByText('Northern Zone')).toBeInTheDocument();
    // make a random change, save the service body, and make sure that the Northern Zone admin is still in the list of meeting editors
    const email = screen.getByRole('textbox', { name: 'Email' }) as HTMLInputElement;
    await user.clear(email);
    await user.type(email, 'morerural@bmlt.app');
    const applyChanges = screen.getByRole('button', { name: 'Apply Changes' });
    await user.click(applyChanges);
    expect(mockSavedServiceBodyUpdate?.assignedUserIds).toEqual(expect.arrayContaining([2]));
  });

  test('logged in as Northern Zone; edit Mountain Area Service Body shows self in Other Meeting Editors', async () => {
    // Northern Zone Admin is explicitly listed as an editor of Mountain Area but the editor multi-select filters out
    // the logged-in user. Without the visibility-based hidden-editors check, Northern Zone Admin would not appear in
    // either list even though they're a real editor of this service body.
    const user = await login('NorthernZone', 'Service Bodies');
    await user.click(await screen.findByRole('cell', { name: 'Mountain Area' }));
    const hiddenSelect = document.querySelector('select[name="assignedUserIds"]') as HTMLSelectElement;
    expect(Array.from(hiddenSelect.selectedOptions).map((o) => o.value)).toEqual([]);
    const otherEditorsLabel = await screen.findByText('Other Meeting Editors');
    const otherEditorsBadges = otherEditorsLabel.parentElement as HTMLElement;
    expect(within(otherEditorsBadges).getByText('Northern Zone')).toBeInTheDocument();
  });

  test('logged in as serveradmin; delete Small Region Service Body', async () => {
    const user = await login('serveradmin', 'Service Bodies');
    await user.click(await screen.findByRole('button', { name: 'Delete Service Body Small Region' }));

    // Wait for meetings to load - Small Region has 1 meeting
    await waitFor(() => {
      expect(screen.getByText(/1 meetings will be deleted/)).toBeInTheDocument();
    });

    // Get all checkboxes (force delete + confirmation)
    const checkboxes = await screen.findAllByRole('checkbox');

    // Check both force delete and confirmation
    await user.click(checkboxes[0]); // force delete
    await user.click(checkboxes[1]); // confirmation

    await user.click(await screen.findByRole('button', { name: 'Delete' }));
    expect(mockDeletedServiceBodyId).toBe(103);
    expect(mockSavedServiceBodyCreate).toBe(null);
    expect(mockSavedServiceBodyUpdate).toBe(null);
  });

  test('logged in as serveradmin; try to delete Big Region Service Body', async () => {
    // this should fail because Big Region has children
    const user = await login('serveradmin', 'Service Bodies');
    await user.click(await screen.findByRole('button', { name: 'Delete Service Body Big Region' }));

    // Wait for meetings to load - Big Region has 1 meeting
    await waitFor(() => {
      expect(screen.getByText(/1 meetings will be deleted/)).toBeInTheDocument();
    });

    // Get all checkboxes (force delete + confirmation)
    const checkboxes = await screen.findAllByRole('checkbox');

    // Check both force delete and confirmation
    await user.click(checkboxes[0]); // force delete
    await user.click(checkboxes[1]); // confirmation

    await user.click(await screen.findByRole('button', { name: 'Delete' }));
    expect(screen.getByText(/Error: The service body could not be deleted/)).toBeInTheDocument();
    expect(mockDeletedServiceBodyId).toBe(null);
    expect(mockSavedServiceBodyCreate).toBe(null);
    expect(mockSavedServiceBodyUpdate).toBe(null);
  });

  test('logged in as serveradmin; delete Rural Area Service Body with meetings shows meeting count and force delete option', async () => {
    const user = await login('serveradmin', 'Service Bodies');
    await user.click(await screen.findByRole('button', { name: 'Delete Service Body Rural Area' }));

    // Wait for meetings to load
    await waitFor(() => {
      expect(screen.getByText(/2 meetings will be deleted/)).toBeInTheDocument();
    });

    // Should show meeting names
    expect(screen.getByText('Real Talk')).toBeInTheDocument();
    expect(screen.getByText('Country Recovery')).toBeInTheDocument();

    // Force delete checkbox should be present
    const checkboxes = screen.getAllByRole('checkbox');
    expect(checkboxes.length).toBe(2); // confirmation + force delete

    // Delete button should be disabled until both checkboxes are checked
    const deleteButton = screen.getByRole('button', { name: 'Delete' });
    expect(deleteButton).toBeDisabled();

    // Check the "Yes, I'm sure" checkbox - delete should still be disabled
    await user.click(checkboxes[1]); // confirmation checkbox
    expect(deleteButton).toBeDisabled();

    // Check the force delete checkbox - now delete should be enabled
    await user.click(checkboxes[0]); // force delete checkbox
    expect(deleteButton).toBeEnabled();

    await user.click(deleteButton);
    expect(mockDeletedServiceBodyId).toBe(106); // Rural Area ID
    expect(mockSavedServiceBodyCreate).toBe(null);
    expect(mockSavedServiceBodyUpdate).toBe(null);
  });

  test('test Confirm modal appears when attempting to click outside modal with unsaved changes', async () => {
    const user = await login('serveradmin', 'Service Bodies');
    await user.click(await screen.findByRole('cell', { name: 'Rural Area' }));
    const helpline = screen.getByRole('textbox', { name: 'Helpline' }) as HTMLInputElement;
    await user.clear(helpline);
    await user.type(helpline, '555-867-5309');
    const outsideElement = document.body;
    await user.click(outsideElement);
    expect(screen.getByText('You have unsaved changes. Do you really want to close?')).toBeInTheDocument();
  });

  test('test Confirm modal appears when attempting to close with unsaved changes', async () => {
    const user = await login('serveradmin', 'Service Bodies');
    await user.click(await screen.findByRole('cell', { name: 'Rural Area' }));
    const helpline = screen.getByRole('textbox', { name: 'Helpline' }) as HTMLInputElement;
    await user.clear(helpline);
    await user.type(helpline, '555-867-5309');
    // There are two close buttons at this point: one for the modal as a whole, the other for the Meeting List Editors multiselect.
    // Mock clicking either one closes the modal, but the second one is for the modal as a whole, so use that.  (If there were no
    // meeting list editors then there would be only one close button, but Rural Area does have meeting list editors.)
    const buttons = await screen.findAllByRole('button', { name: 'Close' });
    await user.click(buttons[0]);
    expect(screen.getByText('You have unsaved changes. Do you really want to close?')).toBeInTheDocument();
  });
});

describe('Spreadsheet download functionality', () => {
  test('Download Spreadsheet button is present when logged in as serveradmin', async () => {
    await login('serveradmin', 'Service Bodies');
    const downloadButton = await screen.findByRole('button', { name: /Download Spreadsheet/i });
    expect(downloadButton).toBeInTheDocument();
    expect(downloadButton).toBeEnabled();
  });

  test('Download Spreadsheet button triggers download when clicked', async () => {
    const user = await login('serveradmin', 'Service Bodies');
    const downloadButton = await screen.findByRole('button', { name: /Download Spreadsheet/i });
    await user.click(downloadButton);

    await waitFor(() => {
      expect(xlsxWriteFileSpy).toHaveBeenCalled();
    });

    const filename = serviceBodiesFile;
    expect(filename).toMatch(/^service_bodies_\d{4}_\d{2}_\d{2}_\d{2}_\d{2}_\d{2}\.xlsx$/);
  });

  test('Download Spreadsheet contains correct service body data', async () => {
    const user = await login('serveradmin', 'Service Bodies');
    const downloadButton = await screen.findByRole('button', { name: /Download Spreadsheet/i });
    await user.click(downloadButton);

    await waitFor(() => {
      expect(xlsxWriteFileSpy).toHaveBeenCalled();
    });

    // Check workbook structure and content
    expect(serviceBodiesWB).not.toBe(null);
    const sheet = serviceBodiesWB?.Sheets.Sheet1;
    expect(sheet).not.toBe(undefined);

    if (sheet) {
      // Check headers
      expect(sheet['A1'].v).toBe('id');
      expect(sheet['B1'].v).toBe('name');
      expect(sheet['C1'].v).toBe('description');
      expect(sheet['D1'].v).toBe('type');
      expect(sheet['E1'].v).toBe('adminUserId');
      expect(sheet['F1'].v).toBe('parentId');
      expect(sheet['G1'].v).toBe('worldId');
      expect(sheet['H1'].v).toBe('url');
      expect(sheet['I1'].v).toBe('helpline');
      expect(sheet['J1'].v).toBe('email');

      // Check for some known service body data (from shared mocks)
      const range = XLSX.utils.decode_range(sheet['!ref'] || 'A1');
      let foundBigRegion = false;
      let foundRuralArea = false;

      // Check rows (starting from row 2, since row 1 is headers)
      for (let row = 2; row <= range.e.r + 1; row++) {
        const name = sheet[`B${row}`]?.v;
        const type = sheet[`D${row}`]?.v;
        const email = sheet[`J${row}`]?.v;

        if (name === 'Big Region' && email === 'big@bmlt.app') {
          foundBigRegion = true;
          expect(sheet[`C${row}`].v).toBe('Big Region Description'); // description
          expect(type).toBe('RG'); // type
        }

        if (name === 'Rural Area' && email === 'rural@bmlt.app') {
          foundRuralArea = true;
          expect(sheet[`C${row}`].v).toBe('Rural Area Description'); // description
          expect(type).toBe('AS'); // type
          expect(sheet[`G${row}`].v).toBe('AS778'); // worldId
          expect(sheet[`H${row}`].v).toBe('https://ruralarea.example.com'); // url
          expect(sheet[`I${row}`].v).toBe('803-555-7247'); // helpline
        }
      }

      expect(foundBigRegion).toBe(true);
      expect(foundRuralArea).toBe(true);
    }
  });

  test('Download Spreadsheet button is not present when logged in as non-admin', async () => {
    await login('NorthernZone', 'Service Bodies');
    const cells = await screen.findAllByRole('cell');
    expect(cells.length).toBe(6);
    const downloadButton = screen.queryByRole('button', { name: /Download Spreadsheet/i });
    expect(downloadButton).not.toBeInTheDocument();
  });

  test('displays service body ID for existing service body', async () => {
    const user = await login('serveradmin', 'Service Bodies');
    await user.click(await screen.findByRole('cell', { name: 'Rural Area' }));

    await waitFor(() => {
      expect(screen.getByText('Service Body ID:')).toBeInTheDocument();
      expect(screen.getByText('106')).toBeInTheDocument(); // Rural Area ID
    });
  });

  test('service body ID is displayed on the right side', async () => {
    const user = await login('serveradmin', 'Service Bodies');
    await user.click(await screen.findByRole('cell', { name: 'Rural Area' }));

    await waitFor(() => {
      const serviceBodyIdElement = screen.getByText('Service Body ID:').closest('div');
      expect(serviceBodyIdElement).toHaveClass('ml-auto');
    });
  });

  test('service body ID is not displayed for new service body', async () => {
    const user = await login('serveradmin', 'Service Bodies');
    await user.click(await screen.findByRole('button', { name: 'Add Service Body' }));

    expect(screen.queryByText('Service Body ID:')).not.toBeInTheDocument();
  });
});
