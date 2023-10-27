import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { ResponseError, Token, User } from 'bmlt-root-server-client';
import { describe, test, vi } from 'vitest';

import App from '../App';
import ApiClientWrapper from '../RootServerApi';

// we only want to mock methods on the instance of ApiClient stored in ApiClientWrapper.api
// we're not mocking methods on the wrapper itself (ApiClientWrapper)
vi.mock('../RootServerApi', async () => {
  const mod = await vi.importActual<typeof import('../RootServerApi')>('../RootServerApi');
  return {
    ...mod,
    mocked: vi.fn(),
  };
});

// define a mock authToken that expires 1 hour from now
// (the token uses PHP's time rather than Javascript's time, so seconds from the epoch instead of milliseconds)
const now: number = Math.round(new Date().valueOf() / 1000);
const mockedToken: Token = {
  accessToken: 'mysteryString42',
  expiresAt: now + 60 * 60,
  tokenType: 'bearer',
  userId: 1,
};

// for now just define a serveradmin user
const mockUser: User = {
  description: 'Main Server Administrator',
  displayName: 'Server Administrator',
  email: 'nobody@bmlt.app',
  id: 1,
  ownerId: '',
  type: 'admin',
  username: 'serveradmin',
};

// mocked access token
let savedAccessToken: Token | null;

function mockGetToken(): Token | null {
  return savedAccessToken;
}

function mockSetToken(token: Token | null): void {
  savedAccessToken = token;
}

async function mockGetUser(params: { userId: number }): Promise<User> {
  if (params.userId == mockUser.id) {
    return mockUser;
  }
  throw new Error('no user found with the given userId');
}

function mockIsLoggedIn(): boolean {
  return Boolean(savedAccessToken);
}

async function mockAuthToken(authTokenRequest: { tokenCredentials: { username: string; password: string } }): Promise<Token> {
  if (authTokenRequest.tokenCredentials.username === 'serveradmin' && authTokenRequest.tokenCredentials.password === 'good-password') {
    return mockedToken;
  } else {
    const msg = '{ "message": "The provided credentials are incorrect." }';
    const unicodeMsg = Uint8Array.from(Array.from(msg).map((x) => x.charCodeAt(0)));
    const strm = new ReadableStream({
      start(controller) {
        controller.enqueue(unicodeMsg);
        controller.close();
      },
    });
    const r: Response = new Response(strm, { status: 401, statusText: 'Unauthorized' });
    throw new ResponseError(r, 'Response returned an error code');
  }
}

async function mockAuthLogout(): Promise<void> {
  savedAccessToken = null;
}

vi.spyOn(ApiClientWrapper.api, 'token', 'get').mockImplementation(mockGetToken);
vi.spyOn(ApiClientWrapper.api, 'token', 'set').mockImplementation(mockSetToken);
vi.spyOn(ApiClientWrapper.api, 'isLoggedIn', 'get').mockImplementation(mockIsLoggedIn);
vi.spyOn(ApiClientWrapper.api, 'getUser').mockImplementation(mockGetUser);
vi.spyOn(ApiClientWrapper.api, 'authToken').mockImplementation(mockAuthToken);
vi.spyOn(ApiClientWrapper.api, 'authLogout').mockImplementation(mockAuthLogout);

describe('Login', () => {
  test('check login page before logging in', () => {
    savedAccessToken = null;
    render(<App />);
    expect(screen.getByRole('heading', { level: 1 })).toHaveTextContent('Root Server');
    expect(screen.getByRole('heading', { level: 3 })).toHaveTextContent('Login');
    expect(screen.getByRole('textbox', { name: 'Username' })).toBeInTheDocument();
    expect(screen.getByLabelText(/password/i)).toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'Log In' })).toBeEnabled();
  });

  test('missing username', async () => {
    savedAccessToken = null;
    const user = userEvent.setup();
    render(<App />);
    await user.type(screen.getByLabelText(/password/i), 'good-password');
    await user.click(screen.getByRole('button', { name: 'Log In' }));
    expect(screen.getByText(/Username is required/)).toBeInTheDocument();
  });

  test('missing password', async () => {
    savedAccessToken = null;
    const user = userEvent.setup();
    render(<App />);
    await user.type(screen.getByRole('textbox', { name: 'Username' }), 'serveradmin');
    await user.click(screen.getByRole('button', { name: 'Log In' }));
    expect(screen.getByText(/Password is required/)).toBeInTheDocument();
  });

  test('invalid password', async () => {
    savedAccessToken = null;
    const user = userEvent.setup();
    render(<App />);
    await user.type(screen.getByRole('textbox', { name: 'Username' }), 'serveradmin');
    await user.type(screen.getByLabelText(/password/i), 'bad-password');
    expect(screen.getByRole('textbox', { name: 'Username' })).toHaveDisplayValue('serveradmin');
    expect(screen.getByLabelText(/password/i)).toHaveDisplayValue('bad-password');
    await user.click(screen.getByRole('button', { name: 'Log In' }));
    await screen.findByText(/The provided credentials are incorrect./);
  });

  test('log in with valid username and password, then log out again', async () => {
    savedAccessToken = null;
    const user = userEvent.setup();
    render(<App />);
    await user.type(screen.getByRole('textbox', { name: 'Username' }), 'serveradmin');
    await user.type(screen.getByLabelText(/password/i), 'good-password');
    expect(screen.getByRole('textbox', { name: 'Username' })).toHaveDisplayValue('serveradmin');
    expect(screen.getByLabelText(/password/i)).toHaveDisplayValue('good-password');
    await user.click(screen.getByRole('button', { name: 'Log In' }));
    // after a successful login, we should see the Dashboard
    await screen.findByText(/Dashboard/i);
    // log out, and make sure we're back at the login screen
    await user.click(screen.getByRole('button', { name: 'Sign Out' }));
    expect(screen.getByRole('heading', { level: 3 })).toHaveTextContent('Login');
  });
});
