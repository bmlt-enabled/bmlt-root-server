import { beforeAll, beforeEach, describe, test } from 'vitest';
import { screen } from '@testing-library/svelte';
import '@testing-library/jest-dom';
import { login, mockDeletedServiceBodyId, mockSavedServiceBodyCreate, mockSavedServiceBodyUpdate, sharedAfterEach, sharedBeforeAll, sharedBeforeEach } from './sharedDataAndMocks';
import userEvent from '@testing-library/user-event';

beforeAll(sharedBeforeAll);
beforeEach(sharedBeforeEach);
afterEach(sharedAfterEach);

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
    // There should be 4 users, with 1 cell per user (display name but no delete icon)
    const cells = await screen.findAllByRole('cell');
    expect(cells.length).toBe(6);
    expect(screen.getByRole('cell', { name: 'Big Region' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'Rural Area' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'Mountain Area' })).toBeInTheDocument();
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
    expect(b.attributes.getNamedItem('class')?.value.includes('cursor-not-allowed')).toBeTruthy();
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
    expect(serviceBodyParent.value).toBe('2'); // id of Big Region
    await userEvent.selectOptions(serviceBodyParent, ['1']);
    expect(serviceBodyParent.value).toBe('1');
    const meetingListEditors = screen.getByLabelText('Meeting List Editors') as HTMLSelectElement;
    const initialSelectedOptions = Array.from(meetingListEditors.selectedOptions).map((option) => option.value);
    expect(initialSelectedOptions).toEqual(['2', '7']);
    await userEvent.selectOptions(meetingListEditors, ['2', '6']);
    const selectedOptions = Array.from(meetingListEditors.selectedOptions).map((option) => option.value);
    expect(selectedOptions).toEqual(['6', '2', '7']);
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
    expect(mockSavedServiceBodyUpdate?.parentId).toBe(1);
    expect(mockSavedServiceBodyUpdate?.assignedUserIds).toStrictEqual([6, 2, 7]);
    expect(mockSavedServiceBodyUpdate?.email).toBe('morerural@bmlt.app');
    expect(mockSavedServiceBodyUpdate?.description).toBe('Rural Area Description now more rural');
    expect(mockSavedServiceBodyUpdate?.url).toBe('https://moreruralarea.example.com');
    expect(mockSavedServiceBodyUpdate?.helpline).toBe('843-555-7247');
    expect(mockSavedServiceBodyUpdate?.worldId).toBe('AS788');

    // check that user create and user delete weren't touched
    expect(mockSavedServiceBodyCreate).toBe(null);
    expect(mockDeletedServiceBodyId).toBe(null);
  });

  test('test Confirm modal appears when attempting to close with unsaved changes', async () => {
    const user = await login('serveradmin', 'Service Bodies');
    await user.click(await screen.findByRole('cell', { name: 'Rural Area' }));
    const helpline = screen.getByRole('textbox', { name: 'Helpline' }) as HTMLInputElement;
    await user.clear(helpline);
    await user.type(helpline, '555-867-5309');
    await user.click(await screen.findByRole('button', { name: 'Close modal' }));
    expect(screen.getByText('You have unsaved changes. Do you really want to close?')).toBeInTheDocument();
  });
});
