import { beforeAll, beforeEach, describe, test, vi } from 'vitest';
import { screen } from '@testing-library/svelte';
import '@testing-library/jest-dom';
import userEvent from '@testing-library/user-event';

import type { User, UserCreate, UserUpdate } from 'bmlt-root-server-client';

import ApiClientWrapper from '../lib/RootServerApi';
import { loginAndOpenTab, setupMocks, sharedAfterEach } from './sharedDataAndMocks';

// in addition to the shared mocks, here we also mock createUser, updateUser, and deleteUser (which are only used by this tab)

let mockSavedUserCreate: UserCreate | null;
let mockSavedUserUpdate: UserUpdate | null;
let mockDeletedUserId: number | null = null;

async function mockCreateUser({ userCreate: user }: { userCreate: UserCreate }): Promise<User> {
  mockSavedUserCreate = user;
  return {
    username: user.username,
    type: user.type,
    displayName: user.displayName,
    description: user.description || '',
    email: user.email || '',
    ownerId: user.ownerId || -1,
    id: 42 // just make up an ID.  This assumes we aren't making more than one user in a test, or saving the new user between tests.
  };
}

// we aren't using the userId in the mock
// eslint-disable-next-line
async function mockUpdateUser({ userId: _, userUpdate: user }: { userId: number; userUpdate: UserUpdate }): Promise<void> {
  mockSavedUserUpdate = user;
}

async function mockDeleteUser({ userId: id }: { userId: number }): Promise<void> {
  mockDeletedUserId = id;
}

beforeAll(async () => {
  setupMocks();
  vi.spyOn(ApiClientWrapper.api, 'createUser').mockImplementation(mockCreateUser);
  vi.spyOn(ApiClientWrapper.api, 'updateUser').mockImplementation(mockUpdateUser);
  vi.spyOn(ApiClientWrapper.api, 'deleteUser').mockImplementation(mockDeleteUser);
});

beforeEach(async () => {
  mockSavedUserCreate = null;
  mockSavedUserUpdate = null;
});

afterEach(async () => {
  await sharedAfterEach();
});

describe('check content in User tab when logged in as various users', () => {
  test('check layout when logged in as serveradmin', async () => {
    await loginAndOpenTab('serveradmin', 'Users');
    expect(await screen.findByRole('heading', { name: 'Users', level: 2 })).toBeInTheDocument();
    expect(await screen.findByRole('textbox', { name: 'Search' })).toBeInTheDocument();
    // There should be 8 users, with 2 cells per user (display name and a delete icon)
    const cells = await screen.findAllByRole('cell');
    expect(cells.length).toBe(16);
    // check for a couple of representative users
    expect(await screen.findByRole('cell', { name: 'Big Region' })).toBeInTheDocument();
    expect(await screen.findByRole('cell', { name: 'Small Observer' })).toBeInTheDocument();
    expect(await screen.findByRole('cell', { name: 'Small Deactivated' })).toBeInTheDocument();
  });

  test('check layout when logged in as Northern Zone', async () => {
    loginAndOpenTab('NorthernZone', 'Users');
    // There should be 4 users, with 1 cell per user (display name but no delete icon)
    const cells = await screen.findAllByRole('cell');
    expect(cells.length).toBe(4);
    expect(await screen.findByRole('cell', { name: 'Big Region' })).toBeInTheDocument();
    expect(await screen.findByRole('cell', { name: 'Small Region' })).toBeInTheDocument();
    expect(await screen.findByRole('cell', { name: 'Small Observer' })).toBeInTheDocument();
    expect(await screen.findByRole('cell', { name: 'Small Deactivated' })).toBeInTheDocument();
  });

  test('check layout when logged in as Big Region', async () => {
    loginAndOpenTab('BigRegion', 'Users');
    // There should be 3 users, with 1 cell per user (display name but no delete icon)
    const cells = await screen.findAllByRole('cell');
    expect(cells.length).toBe(3);
    expect(await screen.findByRole('cell', { name: 'Mountain Area' })).toBeInTheDocument();
    expect(await screen.findByRole('cell', { name: 'River City Area' })).toBeInTheDocument();
    expect(await screen.findByRole('cell', { name: 'Rural Area' })).toBeInTheDocument();
  });

  test('check layout when logged in as Small Region', async () => {
    loginAndOpenTab('SmallRegion', 'Users');
    expect(await screen.findByText('No users found.')).toBeInTheDocument();
  });

  test('check layout when logged in as Small Region Observer', async () => {
    loginAndOpenTab('SmallObserver', 'Users');
    expect(await screen.findByText('No users found.')).toBeInTheDocument();
  });
});

describe('check editing, adding, and deleting users using the popup dialog boxes', () => {
  test('logged in as serveradmin; edit Big Region', async () => {
    // For each field displayed in the popup, check the default contents, edit the field, and check the result.
    // Then save the edits, and check the contents of the User Update request.
    const user = await loginAndOpenTab('serveradmin', 'Users');
    await user.click(await screen.findByRole('cell', { name: 'Big Region' }));
    // The applyChanges button should be disabled at this point since there haven't been any edits.
    // We'll need to find the Apply Changes button again later, after there have been changes -- for some reason it's
    // a different button after it's enabled.
    const b = await screen.findByRole('button', { name: 'Apply Changes' });
    // This test ought to work but doesn't, so check for 'cursor-not-allowed' instead
    // expect(b).toBeDisabled();
    expect(b.attributes.getNamedItem('class')?.value.includes('cursor-not-allowed')).toBeTruthy();
    const userType = (await screen.findByRole('combobox', { name: 'User Type' })) as HTMLSelectElement;
    expect(userType.value).toBe('serviceBodyAdmin');
    await userEvent.selectOptions(userType, ['Observer']);
    expect(userType.value).toBe('observer');
    const ownedBy = (await screen.findByRole('combobox', { name: 'Owned By' })) as HTMLSelectElement;
    expect(ownedBy.value).toBe('2'); // id of Northern Zone
    await userEvent.selectOptions(ownedBy, ['1']);
    expect(ownedBy.value).toBe('1');
    const displayName = (await screen.findByRole('textbox', { name: 'Name' })) as HTMLInputElement;
    expect(displayName.value).toBe('Big Region');
    await user.clear(displayName);
    await user.type(displayName, 'Bigger Region');
    expect(displayName.value).toBe('Bigger Region');
    const email = (await screen.findByRole('textbox', { name: 'Email' })) as HTMLInputElement;
    expect(email.value).toBe('big@bmlt.app');
    await user.clear(email);
    await user.type(email, 'bigger@bmlt.app');
    expect(email.value).toBe('bigger@bmlt.app');
    const description = (await screen.findByRole('textbox', { name: 'Description' })) as HTMLInputElement;
    expect(description.value).toBe('Big Region Administrator');
    // just for variety we don't clear description, just append to it
    await user.type(description, ' now bigger');
    expect(description.value).toBe('Big Region Administrator now bigger');
    const userName = (await screen.findByRole('textbox', { name: 'Username' })) as HTMLInputElement;
    expect(userName.value).toBe('BigRegion');
    await user.clear(userName);
    await user.type(userName, 'BiggerRegion');
    expect(userName.value).toBe('BiggerRegion');
    const password = (await screen.findByLabelText('Password')) as HTMLInputElement;
    expect(password.value).toBe('');
    await user.type(password, 'new password');
    expect(password.value).toBe('new password');
    const applyChanges = await screen.findByRole('button', { name: 'Apply Changes' });
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
    const user = await loginAndOpenTab('serveradmin', 'Users');
    await user.click(await screen.findByRole('button', { name: 'Add User' }));
    // check that the User Type menu is there but don't change the default (we already tested changing it in the update user test)
    const userType = (await screen.findByRole('combobox', { name: 'User Type' })) as HTMLSelectElement;
    expect(userType.value).toBe('serviceBodyAdmin');
    const ownedBy = (await screen.findByRole('combobox', { name: 'Owned By' })) as HTMLSelectElement;
    expect(ownedBy.value).toBe('1'); // id of serveradmin
    await userEvent.selectOptions(ownedBy, ['2']);
    expect(ownedBy.value).toBe('2');
    const displayName = (await screen.findByRole('textbox', { name: 'Name' })) as HTMLInputElement;
    await user.type(displayName, 'Weird Region');
    expect(displayName.value).toBe('Weird Region');
    const email = (await screen.findByRole('textbox', { name: 'Email' })) as HTMLInputElement;
    await user.type(email, 'weird@bmlt.app');
    expect(email.value).toBe('weird@bmlt.app');
    const description = (await screen.findByRole('textbox', { name: 'Description' })) as HTMLInputElement;
    await user.type(description, 'a weird description');
    expect(description.value).toBe('a weird description');
    const userName = (await screen.findByRole('textbox', { name: 'Username' })) as HTMLInputElement;
    await user.type(userName, 'WeirdRegion');
    expect(userName.value).toBe('WeirdRegion');
    const password = (await screen.findByLabelText('Password')) as HTMLInputElement;
    await user.type(password, 'weird password');
    expect(password.value).toBe('weird password');
    // at this point there are *two* 'Add User' buttons.  Click the second one.  (Kind of funky ...)
    const addButtons = await screen.findAllByRole('button', { name: 'Add User' });
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
    const user = await loginAndOpenTab('serveradmin', 'Users');
    await user.click(await screen.findByRole('button', { name: 'Add User' }));
    const password = (await screen.findByLabelText('Password')) as HTMLInputElement;
    await user.type(password, 'short');
    const addButtons = await screen.findAllByRole('button', { name: 'Add User' });
    await user.click(addButtons[1]);
    expect(await screen.findByText('displayName is a required field')).toBeInTheDocument();
    expect(await screen.findByText('username is a required field')).toBeInTheDocument();
    expect(await screen.findByText('password must be between 12 and 255 characters')).toBeInTheDocument();
  });

  test('logged in as Northern Zone; edit Big Region', async () => {
    // We already tested the editing form when logged in as serveradmin.  Here just test that the User Type
    // and Owned By menus are disabled and also hidden, and that one field (Name) is present and enabled.
    const user = await loginAndOpenTab('NorthernZone', 'Users');
    await user.click(await screen.findByRole('cell', { name: 'Big Region' }));
    expect(await screen.findByRole('combobox', { name: 'User Type', hidden: true })).toBeDisabled();
    expect(await screen.findByRole('combobox', { name: 'Owned By', hidden: true })).toBeDisabled();
    expect(await screen.findByRole('textbox', { name: 'Name' })).toBeEnabled();
  });

  test('logged in as serveradmin; delete Small Region', async () => {
    // TODO: finish this test.  mockDeletedUserId should end up set to 4
  });

  test('logged in as serveradmin; try to delete Big Region', async () => {
    // TODO: finish this test.  This should fail because Big Region has children.
  });
});
