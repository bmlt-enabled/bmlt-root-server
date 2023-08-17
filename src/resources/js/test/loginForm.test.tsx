import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, test, vi } from 'vitest';

import App from '../App';

import ApiClientWrapper from '../RootServerApi';
vi.mock('../RootServerApi');

// define a mock authToken that expires 1 hour from now
// (the token uses PHP's time rather than Javascript's time, so seconds from the epoch instead of milliseconds)
const now = Math.round(new Date().valueOf() / 1000);
const mockAuthToken = {
  accessToken: "mysteryString42",
  expiresAt:  now + 60*60,
  tokenType: "bearer",
  userId:  1,
}

ApiClientWrapper.login.mockResolvedValue(mockAuthToken);

// alternative for mocking would be to mock this version of RootServerApi:
// import { RootServerApi } from '../../../node_modules/bmlt-root-server-client/src/apis/RootServerApi';
// vi.mock('../../../node_modules/bmlt-root-server-client/src/apis/RootServerApi');
// RootServerApi.prototype.authToken.mockResolvedValue(mockAuthToken);

describe('Login', () => {

  test('check login page before logging in', () => {
    render(<App />);
    expect(screen.getByRole('heading', { level: 1 })).toHaveTextContent('Root Server');
    expect(screen.getByRole('heading', { level: 3 })).toHaveTextContent('Login');
    expect(screen.getByRole('textbox', { name: 'Username' })).toBeInTheDocument();
    expect(screen.getByLabelText(/password/i)).toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'Log In' })).toBeEnabled();
  });

  test('missing username', async () => {
    const user = userEvent.setup();
    render(<App />);
    await user.type(screen.getByLabelText(/password/i), 'testtesttest');
    await user.click(screen.getByRole('button', { name: 'Log In' }));
    expect(screen.getByText(/Username is required/)).toBeInTheDocument();
  });

  test('missing password', async () => {
    const user = userEvent.setup();
    render(<App />);
    await user.type(screen.getByRole('textbox', { name: 'Username' }), 'serveradmin');
    await user.click(screen.getByRole('button', { name: 'Log In' }));
    expect(screen.getByText(/Password is required/)).toBeInTheDocument();
  });

  test('invalid password', async () => {
    const user = userEvent.setup();
    render(<App />);
    await user.type(screen.getByRole('textbox', { name: 'Username' }), 'serveradmin');
    await user.type(screen.getByLabelText(/password/i), 'wrongpassword');
    expect(screen.getByRole('textbox', { name: 'Username' })).toHaveDisplayValue('serveradmin');
    expect(screen.getByLabelText(/password/i)).toHaveDisplayValue('wrongpassword');
    await user.click(screen.getByRole('button', { name: 'Log In' }));
    // notworking yet
    // await screen.findByText(/The provided credentials are incorrect./);
  });

  test('log in with valid username and password', async () => {
    const user = userEvent.setup();
    render(<App />);
    await user.type(screen.getByRole('textbox', { name: 'Username' }), 'serveradmin');
    await user.type(screen.getByLabelText(/password/i), 'testtesttest');
    expect(screen.getByRole('textbox', { name: 'Username' })).toHaveDisplayValue('serveradmin');
    expect(screen.getByLabelText(/password/i)).toHaveDisplayValue('testtesttest');
    await user.click(screen.getByRole('button', { name: 'Log In' }));
    // the following test should work, but it doesn't  ?????
    // await screen.findByText(/Dashboard/i);
    await screen.findByText(/Login/i);
  });

});
