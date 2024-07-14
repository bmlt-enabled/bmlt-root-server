import { render, screen } from '@testing-library/svelte';
import userEvent from '@testing-library/user-event';
import '@testing-library/jest-dom';
import { beforeAll, beforeEach, describe, expect, test } from 'vitest';
import App from '../App.svelte';
import { setupMocks, sharedBeforeEach } from './sharedDataAndMocks';

beforeAll(async () => {
  setupMocks();
});

beforeEach(async () => {
  sharedBeforeEach();
});

describe('login page tests', () => {
  test('check login page before logging in', async () => {
    render(App);
    expect(document.title).toBe('BMLT Root Server');
    expect(await screen.findByText('Root Server (1.0.0)')).toBeInTheDocument();
    expect(await screen.findByRole('textbox', { name: 'Username' })).toBeInTheDocument();
    expect(await screen.findByLabelText('Password')).toBeInTheDocument();
    expect(await screen.findByRole('combobox', { name: 'Select Language' })).toBeInTheDocument();
    expect(await screen.findByRole('button', { name: 'Log In' })).toBeEnabled();
    expect(await screen.findByRole('button', { name: 'Dark mode' })).toBeEnabled();
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
    await user.type(await screen.findByLabelText('Password'), 'bad-password');
    expect(await screen.findByRole('textbox', { name: 'Username' })).toHaveDisplayValue('serveradmin');
    expect(await screen.findByLabelText('Password')).toHaveDisplayValue('bad-password');
    await user.click(await screen.findByRole('button', { name: 'Log In' }));
    await screen.findByText('Invalid username or password.');
  });

  test('log in with valid username and password for the server administrator, then log out', async () => {
    const user = userEvent.setup();
    render(App);
    await user.type(await screen.findByRole('textbox', { name: 'Username' }), 'serveradmin');
    await user.type(await screen.findByLabelText('Password'), 'serveradmin-password');
    expect(await screen.findByRole('textbox', { name: 'Username' })).toHaveDisplayValue('serveradmin');
    expect(await screen.findByLabelText('Password')).toHaveDisplayValue('serveradmin-password');
    await user.click(await screen.findByRole('button', { name: 'Log In' }));
    // after a successful login, we should see 'Welcome Server Administrator' and the navbar
    await screen.findByText('Welcome Server Administrator');
    // check for one element in the navbar
    // TODO: shouldn't need to say hidden!
    expect(await screen.findByRole('link', { name: 'Users', hidden: true })).toBeEnabled();
    // log out, and make sure we're back at the login screen
    await user.click(await screen.findByRole('link', { name: 'Logout', hidden: true }));
    expect(await screen.findByRole('button', { name: 'Log In' })).toBeEnabled();
  });

  test('log in with valid username and password for an area administrator', async () => {
    const user = userEvent.setup();
    render(App);
    await user.type(await screen.findByRole('textbox', { name: 'Username' }), 'RiverCityArea');
    await user.type(await screen.findByLabelText('Password'), 'river-city-area-password');
    await user.click(await screen.findByRole('button', { name: 'Log In' }));
    // after a successful login, we should see 'Welcome River City Area' and the navbar
    await screen.findByText('Welcome River City Area');
    // TODO: shouldn't need to say hidden!
    expect(await screen.findByRole('link', { name: 'Users', hidden: true })).toBeEnabled();
  });

  test('log in with valid username and password for an observer', async () => {
    const user = userEvent.setup();
    render(App);
    await user.type(await screen.findByRole('textbox', { name: 'Username' }), 'SmallObserver');
    await user.type(await screen.findByLabelText('Password'), 'small-region-observer-password');
    await user.click(await screen.findByRole('button', { name: 'Log In' }));
    // after a successful login, we should see 'Welcome Small Observer' and the navbar
    await screen.findByText('Welcome Small Observer');
    // TODO: probably the UI should be changed so that Users isn't visible, or is greyed out, for observers
    expect(await screen.findByRole('link', { name: 'Users', hidden: true })).toBeEnabled();
  });

  test('log in with valid username and password for a deactivated user', async () => {
    const user = userEvent.setup();
    render(App);
    await user.type(await screen.findByRole('textbox', { name: 'Username' }), 'SmallDeactivated');
    await user.type(await screen.findByLabelText('Password'), 'small-region-deactivated-password');
    await user.click(await screen.findByRole('button', { name: 'Log In' }));
    expect(await screen.findByText('User is deactivated.')).toBeInTheDocument();
  });
});
