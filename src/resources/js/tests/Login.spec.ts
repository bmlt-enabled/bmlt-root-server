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
  test('check login page before logging in', () => {
    render(App);
    expect(document.title).toBe('BMLT Root Server');
    expect(screen.getByText('Root Server (1.0.0)')).toBeInTheDocument();
    expect(screen.getByRole('textbox', { name: 'Username' })).toBeInTheDocument();
    expect(screen.getByLabelText('Password')).toBeInTheDocument();
    expect(screen.getByRole('combobox', { name: 'Select Language' })).toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'Log In' })).toBeEnabled();
    expect(screen.getByRole('button', { name: 'Dark mode' })).toBeEnabled();
  });

  test('missing username', async () => {
    const user = userEvent.setup();
    render(App);
    await user.type(screen.getByLabelText('Password'), 'serveradmin-password');
    await user.click(screen.getByRole('button', { name: 'Log In' }));
    expect(screen.getByText('username is a required field')).toBeInTheDocument();
  });

  test('missing password', async () => {
    const user = userEvent.setup();
    render(App);
    await user.type(screen.getByRole('textbox', { name: 'Username' }), 'serveradmin');
    await user.click(screen.getByRole('button', { name: 'Log In' }));
    expect(screen.getByText('password is a required field')).toBeInTheDocument();
  });

  test('invalid password', async () => {
    const user = userEvent.setup();
    render(App);
    await user.type(screen.getByRole('textbox', { name: 'Username' }), 'serveradmin');
    await user.type(screen.getByLabelText('Password'), 'bad-password');
    expect(screen.getByRole('textbox', { name: 'Username' })).toHaveDisplayValue('serveradmin');
    expect(screen.getByLabelText('Password')).toHaveDisplayValue('bad-password');
    await user.click(screen.getByRole('button', { name: 'Log In' }));
    await screen.findByText('Invalid username or password.');
  });

  test('log in with valid username and password for the server administrator, then log out', async () => {
    const user = userEvent.setup();
    render(App);
    await user.type(screen.getByRole('textbox', { name: 'Username' }), 'serveradmin');
    await user.type(screen.getByLabelText('Password'), 'serveradmin-password');
    expect(screen.getByRole('textbox', { name: 'Username' })).toHaveDisplayValue('serveradmin');
    expect(screen.getByLabelText('Password')).toHaveDisplayValue('serveradmin-password');
    await user.click(screen.getByRole('button', { name: 'Log In' }));
    // after a successful login, we should see 'Welcome Server Administrator' and the navbar
    await screen.findByText('Welcome Server Administrator');
    // check for one element in the navbar
    // TODO: shouldn't need to say hidden!
    expect(screen.getByRole('link', { name: 'Users', hidden: true })).toBeEnabled();
    // log out, and make sure we're back at the login screen
    await user.click(screen.getByRole('link', { name: 'Logout', hidden: true }));
    expect(screen.getByRole('button', { name: 'Log In' })).toBeEnabled();
  });

  test('log in with valid username and password for an area administrator', async () => {
    const user = userEvent.setup();
    render(App);
    await user.type(screen.getByRole('textbox', { name: 'Username' }), 'RiverCityArea');
    await user.type(screen.getByLabelText('Password'), 'river-city-area-password');
    await user.click(screen.getByRole('button', { name: 'Log In' }));
    // after a successful login, we should see 'Welcome River City Area' and the navbar
    await screen.findByText('Welcome River City Area');
    // TODO: shouldn't need to say hidden!
    expect(screen.getByRole('link', { name: 'Users', hidden: true })).toBeEnabled();
  });

  test('log in with valid username and password for an observer', async () => {
    const user = userEvent.setup();
    render(App);
    await user.type(screen.getByRole('textbox', { name: 'Username' }), 'SmallObserver');
    await user.type(screen.getByLabelText('Password'), 'small-region-observer-password');
    await user.click(screen.getByRole('button', { name: 'Log In' }));
    // after a successful login, we should see 'Welcome Small Observer' and the navbar
    await screen.findByText('Welcome Small Observer');
    // TODO: probably the UI should be changed so that Users isn't visible, or is greyed out, for observers
    expect(screen.getByRole('link', { name: 'Users', hidden: true })).toBeEnabled();
  });
  // TODO: need to add a test for a deactivated user
});
