import { beforeAll, beforeEach, describe, test } from 'vitest';
import { screen, waitFor } from '@testing-library/svelte';
import '@testing-library/jest-dom';

import { badLogin, login, mockSavedUserPartialUpdate, sharedAfterEach, sharedBeforeAll, sharedBeforeEach } from './sharedDataAndMocks';

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
    const expand = screen.getByRole('button', { name: /toggle accordion/i });
    await user.click(expand);
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

describe('check lists of service bodies different users can edit', () => {
  test('check toggling the accordion', async () => {
    const user = await login('serveradmin', 'Account');
    const toggle = await screen.findByRole('button', { name: /toggle accordion/i });
    expect(toggle.ariaExpanded).toBe('false');
    // TODO: this test fails -- toBeVisible seems to be always true, even if the accordion is collapsed
    // expect(await screen.findByText('Northern Zone')).not.toBeVisible();
    await user.click(toggle);
    expect(toggle.ariaExpanded).toBe('true');
    expect(await screen.findByText('Northern Zone')).toBeVisible();
  });

  test('check serveradmin account', async () => {
    await login('serveradmin', 'Account');
    await waitFor(() => {
      expect(screen.queryByText('Northern Zone')).toBeInTheDocument();
      expect(screen.queryByText('Big Region')).toBeInTheDocument();
      expect(screen.queryByText('Small Region')).toBeInTheDocument();
      expect(screen.queryByText('River City Area')).toBeInTheDocument();
      expect(screen.queryByText('Mountain Area')).toBeInTheDocument();
      expect(screen.queryByText('Rural Area')).toBeInTheDocument();
    });
  });

  test('check Northern Zone admin', async () => {
    await login('NorthernZone', 'Account');
    await waitFor(() => {
      expect(screen.queryByText('Northern Zone')).toBeInTheDocument();
      expect(screen.queryByText('Big Region')).toBeInTheDocument();
      expect(screen.queryByText('Small Region')).toBeInTheDocument();
      expect(screen.queryByText('River City Area')).toBeInTheDocument();
      expect(screen.queryByText('Mountain Area')).toBeInTheDocument();
      expect(screen.queryByText('Rural Area')).toBeInTheDocument();
    });
  });

  test('check Big Region admin', async () => {
    await login('BigRegion', 'Account');
    await waitFor(() => {
      expect(screen.queryByText('Northern Zone')).toBe(null);
      expect(screen.queryByText('Big Region')).toBeInTheDocument();
      expect(screen.queryByText('Small Region')).toBe(null);
      expect(screen.queryByText('River City Area')).toBeInTheDocument();
      expect(screen.queryByText('Mountain Area')).toBeInTheDocument();
      expect(screen.queryByText('Rural Area')).toBeInTheDocument();
    });
  });

  test('check Big Region admin 2', async () => {
    await login('BigRegion2', 'Account');
    await waitFor(() => {
      expect(screen.queryByText('Northern Zone')).toBe(null);
      expect(screen.queryByText('Big Region')).toBeInTheDocument();
      expect(screen.queryByText('Small Region')).toBe(null);
      expect(screen.queryByText('River City Area')).toBeInTheDocument();
      expect(screen.queryByText('Mountain Area')).toBeInTheDocument();
      expect(screen.queryByText('Rural Area')).toBeInTheDocument();
    });
  });

  test('check Small Region admin', async () => {
    await login('SmallRegion', 'Account');
    await waitFor(() => {
      expect(screen.queryByText('Northern Zone')).toBe(null);
      expect(screen.queryByText('Big Region')).toBe(null);
      expect(screen.queryByText('Small Region')).toBeInTheDocument();
      expect(screen.queryByText('River City Area')).toBe(null);
      expect(screen.queryByText('Mountain Area')).toBe(null);
      expect(screen.queryByText('Rural Area')).toBe(null);
    });
  });

  test('check Small Region observer', async () => {
    await login('SmallObserver', 'Account');
    await waitFor(() => {
      expect(screen.queryByText('- None -')).toBeInTheDocument();
    });
  });

  test('check error handling for editable service bodies', async () => {
    await badLogin('NorthernZone', 'Account');
    await waitFor(() => {
      expect(screen.queryByText('bad user -- unable to get service bodies')).toBeInTheDocument();
    });
  });
});
