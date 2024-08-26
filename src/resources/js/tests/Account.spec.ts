import { beforeAll, beforeEach, describe, test } from 'vitest';
import { screen } from '@testing-library/svelte';
import '@testing-library/jest-dom';

import { login, mockSavedUserPartialUpdate, sharedAfterEach, sharedBeforeAll, sharedBeforeEach } from './sharedDataAndMocks';

beforeAll(sharedBeforeAll);
beforeEach(sharedBeforeEach);
afterEach(sharedAfterEach);

describe('check content in Account tab when logged in as various users', () => {
  test('check layout and form when logged in as serveradmin', async () => {
    const user = await login('serveradmin', 'Account');
    expect(await screen.findByRole('heading', { name: 'Account Settings', level: 2 })).toBeInTheDocument();
    const applyChanges = screen.getByRole('button', { name: 'Apply Changes' });
    expect(applyChanges).toBeDisabled();
    const displayName = screen.getByRole('textbox', { name: 'Name' }) as HTMLInputElement;
    expect(displayName.value).toBe('Server Administrator');
    await user.clear(displayName);
    await user.type(displayName, 'Grand Poobah');
    expect(displayName.value).toBe('Grand Poobah');
    const username = screen.getByRole('textbox', { name: 'Username' }) as HTMLInputElement;
    expect(username.value).toBe('serveradmin');
    await user.clear(username);
    await user.type(username, 'serverpoobah');
    expect(username.value).toBe('serverpoobah');
    const accountType = screen.getByRole('textbox', { name: 'Account Type' }) as HTMLInputElement;
    expect(accountType.value).toBe('Main Server Administrator');
    expect(accountType).toBeDisabled();
    const email = screen.getByRole('textbox', { name: 'Email' }) as HTMLInputElement;
    expect(email.value).toBe('mockadmin@bmlt.app');
    await user.clear(email);
    await user.type(email, 'poobah@bmlt.app');
    expect(email.value).toBe('poobah@bmlt.app');
    const description = screen.getByRole('textbox', { name: 'Description' }) as HTMLInputElement;
    expect(description.value).toBe('Main Server Administrator');
    await user.clear(description);
    await user.type(description, 'Main Server Poobah');
    expect(description.value).toBe('Main Server Poobah');
    const password = screen.getByLabelText('Password') as HTMLInputElement;
    expect(password.value).toBe('');
    await user.type(password, 'new password');
    expect(password.value).toBe('new password');
    expect(applyChanges).not.toBeDisabled();
    await user.click(applyChanges);
    expect(applyChanges).toBeDisabled();
    expect(mockSavedUserPartialUpdate?.displayName).toBe('Grand Poobah');
    expect(mockSavedUserPartialUpdate?.email).toBe('poobah@bmlt.app');
    expect(mockSavedUserPartialUpdate?.description).toBe('Main Server Poobah');
    expect(mockSavedUserPartialUpdate?.username).toBe('serverpoobah');
    expect(mockSavedUserPartialUpdate?.password).toBe('new password');
    // Mock clicking the expand icon is not causing the list to expand, so we can't test the list of editable service
    // bodies.  To work around this problem, the service body list is factored out into a separate component
    // (AccountServiceBodyList), and tested separately.
    // TODO: if we can get the simulated click on the expand icon to work, the separate AccountServiceBodyList
    // component could be folded back in.  Although the code is not too bad as is.
    // const expand = screen.getByRole('button', { name: /service bodies this user can edit/i });
    // await user.click(expand);
    // Now make a further change.  The applyChanges button should be enabled again after a further change.
    await user.clear(description);
    await user.type(description, 'Main Server Imperial Wizard');
    expect(description.value).toBe('Main Server Imperial Wizard');
    expect(applyChanges).not.toBeDisabled();
    await user.click(applyChanges);
    expect(applyChanges).toBeDisabled();
    expect(mockSavedUserPartialUpdate?.description).toBe('Main Server Imperial Wizard');
    // other fields in the form should be left intact (including the password)
    expect(mockSavedUserPartialUpdate?.username).toBe('serverpoobah');
    expect(mockSavedUserPartialUpdate?.password).toBe('new password');
  });

  // the following is an edge case -- make sure svelte knows the form is dirty on a second edit just to password
  test('test making an edit to description, saving, then making an edit to just password', async () => {
    // no particular reason to use BigRegion rather than serveradmin -- just tests a different kind of user
    const user = await login('BigRegion', 'Account');
    const description = (await screen.findByRole('textbox', { name: 'Description' })) as HTMLInputElement;
    await user.clear(description);
    await user.type(description, 'Big Region Poobah');
    const applyChanges = screen.getByRole('button', { name: 'Apply Changes' });
    expect(applyChanges).not.toBeDisabled();
    await user.click(applyChanges);
    expect(applyChanges).toBeDisabled();
    expect(mockSavedUserPartialUpdate?.description).toBe('Big Region Poobah');
    const password = screen.getByLabelText('Password') as HTMLInputElement;
    await user.type(password, 'poobah password');
    expect(applyChanges).not.toBeDisabled();
    await user.click(applyChanges);
    expect(applyChanges).toBeDisabled();
    expect(mockSavedUserPartialUpdate?.password).toBe('poobah password');
  });
});
