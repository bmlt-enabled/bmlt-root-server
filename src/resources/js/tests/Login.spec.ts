import { beforeAll, describe, expect, test } from 'vitest';
import { render, screen, waitFor } from '@testing-library/svelte';
import { replace } from 'svelte-spa-router';
import '@testing-library/jest-dom';
import userEvent from '@testing-library/user-event';

import App from '../App.svelte';
import { sharedAfterEach, sharedBeforeAll, sharedBeforeEach } from './sharedDataAndMocks';

beforeAll(sharedBeforeAll);
beforeEach(sharedBeforeEach);
afterEach(sharedAfterEach);

describe('login page tests', () => {
  test('check login page before logging in', async () => {
    render(App);
    await waitFor(() => {
      expect(document.title).toBe('BMLT Root Server');
      expect(screen.getByText('Root Server (1.0.0)')).toBeInTheDocument();
      expect(screen.getByRole('textbox', { name: 'Username' })).toBeInTheDocument();
      expect(screen.getByLabelText('Password')).toBeInTheDocument();
      expect(screen.getByRole('combobox', { name: 'Select Language' })).toBeInTheDocument();
      expect(screen.getByRole('button', { name: 'Log In' })).toBeEnabled();
      expect(screen.getByRole('button', { name: 'Dark mode' })).toBeEnabled();
    });
  });

  test('missing username', async () => {
    const user = userEvent.setup();
    render(App);
    await user.type(await screen.findByLabelText('Password'), 'serveradmin-password');
    await user.click(await screen.findByRole('button', { name: 'Log In' }));
    expect(await screen.findByText('username is a required field')).toBeInTheDocument();
  });

  test('missing password', async () => {
    const user = userEvent.setup();
    render(App);
    await user.type(await screen.findByRole('textbox', { name: 'Username' }), 'serveradmin');
    await user.click(await screen.findByRole('button', { name: 'Log In' }));
    expect(await screen.findByText('password is a required field')).toBeInTheDocument();
  });

  test('invalid password', async () => {
    const user = userEvent.setup();
    render(App);
    await user.type(await screen.findByRole('textbox', { name: 'Username' }), 'serveradmin');
    await user.type(screen.getByLabelText('Password'), 'bad-password');
    expect(screen.getByRole('textbox', { name: 'Username' })).toHaveDisplayValue('serveradmin');
    expect(screen.getByLabelText('Password')).toHaveDisplayValue('bad-password');
    await user.click(screen.getByRole('button', { name: 'Log In' }));
    expect(screen.getByText('Invalid username or password.')).toBeInTheDocument();
  });

  test('log in with valid username and password for the server administrator, then log out', async () => {
    const user = userEvent.setup();
    render(App);
    await user.type(await screen.findByRole('textbox', { name: 'Username' }), 'serveradmin');
    await user.type(screen.getByLabelText('Password'), 'serveradmin-password');
    expect(screen.getByRole('textbox', { name: 'Username' })).toHaveDisplayValue('serveradmin');
    expect(screen.getByLabelText('Password')).toHaveDisplayValue('serveradmin-password');
    await user.click(screen.getByRole('button', { name: 'Log In' }));
    // after a successful login, we should see 'Welcome Server Administrator'
    expect(screen.getByText('Welcome Server Administrator')).toBeInTheDocument();
    // navbar is tested in a different test (below)
    // log out, and make sure we're back at the login screen
    await user.click(screen.getByRole('link', { name: 'Logout', hidden: true }));
    expect(await screen.findByRole('button', { name: 'Log In' })).toBeEnabled();
  });

  test('log in with valid username and password for an area administrator', async () => {
    const user = userEvent.setup();
    render(App);
    await user.type(await screen.findByRole('textbox', { name: 'Username' }), 'RiverCityArea');
    await user.type(screen.getByLabelText('Password'), 'river-city-area-password');
    await user.click(screen.getByRole('button', { name: 'Log In' }));
    // after a successful login, we should see 'Welcome River City Area' and the navbar
    expect(screen.getByText('Welcome River City Area')).toBeInTheDocument();
    // TODO: shouldn't need to say hidden!
    expect(screen.getByRole('link', { name: 'Users', hidden: true })).toBeEnabled();
  });

  test('log in with valid username and password for an observer', async () => {
    const user = userEvent.setup();
    render(App);
    await user.type(await screen.findByRole('textbox', { name: 'Username' }), 'SmallObserver');
    await user.type(screen.getByLabelText('Password'), 'small-region-observer-password');
    await user.click(screen.getByRole('button', { name: 'Log In' }));
    // after a successful login, we should see 'Welcome Small Observer' and the navbar
    expect(screen.getByText('Welcome Small Observer')).toBeInTheDocument();
    expect(screen.getByRole('link', { name: 'Meetings', hidden: true })).toBeEnabled();
    expect(screen.queryByRole('link', { name: 'Formats', hidden: true })).toBe(null);
    expect(screen.queryByRole('link', { name: 'Service Bodies', hidden: true })).toBe(null);
    expect(screen.queryByRole('link', { name: 'Users', hidden: true })).toBe(null);
    expect(screen.getByRole('link', { name: 'Account', hidden: true })).toBeEnabled();
  });

  test('log in with valid username and password for a deactivated user', async () => {
    const user = userEvent.setup();
    render(App);
    await user.type(await screen.findByRole('textbox', { name: 'Username' }), 'SmallDeactivated');
    await user.type(screen.getByLabelText('Password'), 'small-region-deactivated-password');
    await user.click(screen.getByRole('button', { name: 'Log In' }));
    expect(screen.getByText('User is deactivated.')).toBeInTheDocument();
  });
});

describe('navbar tests', () => {
  // Test all of the navbar links for the serveradmin.  Other tests (above) check that the correct links are present
  // for service body admins and observers.
  test('navbar for serveradmin', async () => {
    const user = userEvent.setup();
    render(App);
    await user.type(await screen.findByRole('textbox', { name: 'Username' }), 'serveradmin');
    await user.type(await screen.findByLabelText('Password'), 'serveradmin-password');
    await user.click(await screen.findByRole('button', { name: 'Log In' }));
    // TODO: shouldn't need to say hidden for each of the links
    await user.click(await screen.findByRole('link', { name: 'Meetings', hidden: true }));
    expect(await screen.findByRole('heading', { level: 2, name: 'Meetings' })).toBeInTheDocument();
    // TODO: the test fails without the replace('/') command.  Is this ok to include?
    replace('/');
    await user.click(await screen.findByRole('link', { name: 'Formats', hidden: true }));
    expect(await screen.findByRole('heading', { level: 2, name: 'Formats' })).toBeInTheDocument();
    replace('/');
    await user.click(await screen.findByRole('link', { name: 'Service Bodies', hidden: true }));
    expect(await screen.findByRole('heading', { level: 2, name: 'Service Bodies' })).toBeInTheDocument();
    replace('/');
    await user.click(await screen.findByRole('link', { name: 'Users', hidden: true }));
    expect(await screen.findByRole('heading', { level: 2, name: 'Users' })).toBeInTheDocument();
    replace('/');
    await user.click(await screen.findByRole('link', { name: 'Account', hidden: true }));
    expect(await screen.findByRole('heading', { level: 2, name: 'Account' })).toBeInTheDocument();
    replace('/');
    await user.click(await screen.findByRole('link', { name: 'Home', hidden: true }));
    expect(await screen.findByRole('heading', { level: 5, name: 'Welcome Server Administrator' })).toBeInTheDocument();
    // Logout is tested above
  });
});
