import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { ResponseError, Token, User } from 'bmlt-root-server-client';
import { afterAll, beforeAll, beforeEach, describe, test, vi } from 'vitest';

import App from '../App';
import ApiClientWrapper from '../RootServerApi';

const mockServerAdmin: User = {
  description: 'Main Server Administrator',
  displayName: 'Server Administrator',
  email: 'nobody@bmlt.app',
  id: 1,
  ownerId: '',
  type: 'admin',
  username: 'serveradmin',
};

const mockAreaAdmin: User = {
  description: 'River City Area Administrator',
  displayName: 'River City Area',
  email: 'nobody@bmlt.app',
  id: 6,
  ownerId: '5',
  type: 'serviceBodyAdmin',
  username: 'RiverCityArea',
};

const allMockUsers = [mockServerAdmin, mockAreaAdmin];
const allMockPasswords = ['serveradmin-password', 'rivercity-password'];

// mocked access token
let savedAccessToken: Token | null;

// define a mock authToken that expires 1 hour from now
function generateMockToken(u: User): Token {
  // the token uses PHP's time rather than Javascript's time, so seconds from the epoch instead of milliseconds
  const now: number = Math.round(new Date().valueOf() / 1000);
  return {
    accessToken: 'mysteryString42',
    expiresAt: now + 60 * 60,
    tokenType: 'bearer',
    userId: u.id,
  };
}

function mockGetToken(): Token | null {
  return savedAccessToken;
}

function mockSetToken(token: Token | null): void {
  savedAccessToken = token;
}

async function mockGetUser(params: { userId: number }): Promise<User> {
  const mockUser = allMockUsers.find((u) => u.id === params.userId);
  if (mockUser) {
    return mockUser;
  }
  throw new Error('unknown user -- something went wrong');
}

function mockIsLoggedIn(): boolean {
  return Boolean(savedAccessToken);
}

async function mockAuthToken(authTokenRequest: { tokenCredentials: { username: string; password: string } }): Promise<Token> {
  const n = authTokenRequest.tokenCredentials.username;
  const p = authTokenRequest.tokenCredentials.password;
  for (let i = 0; i < allMockUsers.length; i++) {
    if (allMockUsers[i].username === n && allMockPasswords[i] === p) {
      return generateMockToken(allMockUsers[i]);
    }
  }
  const msg = '{ "message": "The provided credentials are incorrect." }';
  const unicodeMsg = Uint8Array.from(Array.from(msg).map((x) => x.charCodeAt(0)));
  const strm = new ReadableStream({
    start(controller) {
      controller.enqueue(unicodeMsg);
      controller.close();
    },
  });
  const r = new Response(strm, { status: 401, statusText: 'Unauthorized' });
  throw new ResponseError(r, 'Response returned an error code');
}

async function mockAuthLogout(): Promise<void> {
  savedAccessToken = null;
}

beforeAll(async () => {
  vi.spyOn(ApiClientWrapper.api, 'token', 'get').mockImplementation(mockGetToken);
  vi.spyOn(ApiClientWrapper.api, 'token', 'set').mockImplementation(mockSetToken);
  vi.spyOn(ApiClientWrapper.api, 'isLoggedIn', 'get').mockImplementation(mockIsLoggedIn);
  vi.spyOn(ApiClientWrapper.api, 'getUser').mockImplementation(mockGetUser);
  vi.spyOn(ApiClientWrapper.api, 'authToken').mockImplementation(mockAuthToken);
  vi.spyOn(ApiClientWrapper.api, 'authLogout').mockImplementation(mockAuthLogout);
});

beforeEach(async () => {
  savedAccessToken = null;
});

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
    await user.type(screen.getByLabelText(/password/i), 'serveradmin-password');
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
    await user.type(screen.getByLabelText(/password/i), 'bad-password');
    expect(screen.getByRole('textbox', { name: 'Username' })).toHaveDisplayValue('serveradmin');
    expect(screen.getByLabelText(/password/i)).toHaveDisplayValue('bad-password');
    await user.click(screen.getByRole('button', { name: 'Log In' }));
    await screen.findByText(/The provided credentials are incorrect./);
  });

  test('log in with valid username and password for the server administrator, then log out', async () => {
    const user = userEvent.setup();
    render(<App />);
    await user.type(screen.getByRole('textbox', { name: 'Username' }), 'serveradmin');
    await user.type(screen.getByLabelText(/password/i), 'serveradmin-password');
    expect(screen.getByRole('textbox', { name: 'Username' })).toHaveDisplayValue('serveradmin');
    expect(screen.getByLabelText(/password/i)).toHaveDisplayValue('serveradmin-password');
    await user.click(screen.getByRole('button', { name: 'Log In' }));
    // after a successful login, we should see the dashboard, including the word 'Dashboard' and the user name
    await screen.findByText(/Dashboard/);
    await screen.findByText(/Server Administrator/);
    // log out, and make sure we're back at the login screen
    await user.click(screen.getByRole('button', { name: 'Sign Out' }));
    expect(screen.getByRole('heading', { level: 3 })).toHaveTextContent('Login');
  });

  test('log in with valid username and password for an area administrator', async () => {
    const user = userEvent.setup();
    render(<App />);
    await user.type(screen.getByRole('textbox', { name: 'Username' }), 'RiverCityArea');
    await user.type(screen.getByLabelText(/password/i), 'rivercity-password');
    await user.click(screen.getByRole('button', { name: 'Log In' }));
    // after a successful login, we should see the dashboard, including the word 'Dashboard' and the user name
    await screen.findByText(/Dashboard/);
    await screen.findByText(/River City Area/);
  });
});

afterAll(async () => {
  vi.restoreAllMocks();
});
