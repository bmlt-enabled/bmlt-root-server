import { beforeAll, beforeEach, describe, test } from 'vitest';
import { screen } from '@testing-library/svelte';
import '@testing-library/jest-dom';

import { login, mockSavedUserUpdate, sharedAfterEach, sharedBeforeAll, sharedBeforeEach } from './sharedDataAndMocks';

beforeAll(sharedBeforeAll);
beforeEach(sharedBeforeEach);
afterEach(sharedAfterEach);

describe('check content in Account tab when logged in as various users', () => {
  test('check layout when logged in as serveradmin', async () => {
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
    await user.click(applyChanges);
    expect(mockSavedUserUpdate?.displayName).toBe('Grand Poobah');
    expect(mockSavedUserUpdate?.email).toBe('poobah@bmlt.app');
    expect(mockSavedUserUpdate?.description).toBe('Main Server Poobah');
    expect(mockSavedUserUpdate?.username).toBe('serverpoobah');
    expect(mockSavedUserUpdate?.password).toBe('new password');
    // Clicking the expand icon is not causing the list to expand, so the commented-out tests below on the list of
    // editable services bodies fail.  To work around this problem, the service body list is factored out into a separate
    // component (AccountServiceBodyList), and tested separately.
    // TODO: if we can get the simulated click on the expand icon to work, the separate AccountServiceBodyList list component
    // could be folded back in.  Although the code is not too bad as is.
    // const expand = screen.getByRole('button', { name: /service bodies this user can edit/i });
    // await user.click(expand);
    // TODO: clicking the expand icon is not causing the list to expand, so the following tests fail:
    // (may be able to change to getByText once expanding the list is working)
    // expect(await screen.findByText('Northern Zone')).toBeInTheDocument();
    // expect(await screen.findByText('Big Region')).toBeInTheDocument();
    // expect(await screen.findByText('Small Region')).toBeInTheDocument();
    // expect(await screen.findByText('River City Area')).toBeInTheDocument();
    // expect(await screen.findByText('Mountain Area')).toBeInTheDocument();
    // expect(await screen.findByText('Rural Area')).toBeInTheDocument();
  });
});
