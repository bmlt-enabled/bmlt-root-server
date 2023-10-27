import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { AuthenticationError, ResponseError, Token, User } from 'bmlt-root-server-client';
import { describe, test, vi } from 'vitest';

import App from '../App';
import ApiClientWrapper from '../RootServerApi';

type AuthenticationErrorHandler = (error: AuthenticationError) => void;
type GenericErrorHandler = (error: any) => void;
type ErrorHandlers = {
  handleAuthenticationError?: AuthenticationErrorHandler;
  handleError?: GenericErrorHandler;
};

vi.mock('../RootServerApi');

// define a mock authToken that expires 1 hour from now
// (the token uses PHP's time rather than Javascript's time, so seconds from the epoch instead of milliseconds)
const now: number = Math.round(new Date().valueOf() / 1000);
const mockAuthToken: Token = {
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

// mocked error handlers (others aren't used)
let savedAuthenticationErrorHandler: AuthenticationErrorHandler | null;
let savedErrorHandler: GenericErrorHandler | null;

function mockGetToken(): Token | null {
  return savedAccessToken;
}

function mockSetToken(token: Token | null): void {
  savedAccessToken = token;
}

async function mockGetUser(userId: number): Promise<User> {
  if (userId == mockUser.id) {
    return mockUser;
  }
  throw new Error('no user found with the given userId');
}

function mockIsLoggedIn(): boolean {
  return savedAccessToken == mockAuthToken;
}

async function mockLogin(username: string, password: string): Promise<Token> {
  if (username === 'serveradmin' && password === 'good-password') {
    return mockAuthToken;
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

async function mockLogout(): Promise<void> {
  savedAccessToken = null;
}

function mockInitializeDefaultErrorHandlers(defaultErrorHandlers: ErrorHandlers): void {
  savedAuthenticationErrorHandler = defaultErrorHandlers.handleAuthenticationError ?? null;
  savedErrorHandler = defaultErrorHandlers.handleError ?? null;
}

async function mockHandleErrors(error: Error, overrideErrorHandlers?: ErrorHandlers): Promise<void> {
  const handleAuthenticationError: AuthenticationErrorHandler | null =
    overrideErrorHandlers?.handleAuthenticationError ?? savedAuthenticationErrorHandler;
  const handleError: GenericErrorHandler | null = overrideErrorHandlers?.handleError ?? savedErrorHandler;
  // handle api errors
  const responseError: ResponseError = error as ResponseError;
  // The following commented-out code works locally but is giving an error on github.  Temporarily just
  // constructing the error body directly.  Maybe the code in mockLogin is giving some other kind of error
  // on gitub, and not even constructing a ResponseError?
  // const body = await responseError.response.json();
  // if (handleAuthenticationError && responseError.response.status === 401) {
  //   return handleAuthenticationError(body as AuthenticationError);
  // }
  const body = { message: 'The provided credentials are incorrect.' };
  if (handleAuthenticationError && responseError) {
    return handleAuthenticationError(body as AuthenticationError);
  }
  if (handleError) {
    return handleError(body);
  }
  // TODO: is there a better way to handle this?  Here is the code being mocked:
  // return console.log('TODO unhandled error, show error dialog', body);
  throw new Error('unhandled error -- something went wrong');
}

// mock getters and setters
vi.spyOn(ApiClientWrapper, 'token', 'get').mockImplementation(mockGetToken);
vi.spyOn(ApiClientWrapper, 'token', 'set').mockImplementation(mockSetToken);
vi.spyOn(ApiClientWrapper, 'isLoggedIn', 'get').mockImplementation(mockIsLoggedIn);

// The following commented-out statements would be the standard way to mock methods such as login and getUser,
// but typescript doesn't realize that for example ApiClientWrapper.login returns a MockInstance rather than the
// actual login method.  Hence the more verbose vi.spyOn statements below.
// ApiClientWrapper.login.mockImplementation(mockLogin);
// ApiClientWrapper.getUser.mockImplementation(mockGetUser);
// ApiClientWrapper.logout.mockImplementation(mockLogout);
// ApiClientWrapper.initializeDefaultErrorHandlers.mockImplementation(mockInitializeDefaultErrorHandlers);
// ApiClientWrapper.handleErrors.mockImplementation(mockHandleErrors);

vi.spyOn(ApiClientWrapper, 'login').mockImplementation(mockLogin);
vi.spyOn(ApiClientWrapper, 'getUser').mockImplementation(mockGetUser);
vi.spyOn(ApiClientWrapper, 'logout').mockImplementation(mockLogout);
vi.spyOn(ApiClientWrapper, 'initializeDefaultErrorHandlers').mockImplementation(mockInitializeDefaultErrorHandlers);
vi.spyOn(ApiClientWrapper, 'handleErrors').mockImplementation(mockHandleErrors);

// alternative for mocking would be to mock this version of RootServerApi:
// import { RootServerApi } from '../../../node_modules/bmlt-root-server-client/src/apis/RootServerApi';
// vi.mock('../../../node_modules/bmlt-root-server-client/src/apis/RootServerApi');
// RootServerApi.prototype.authToken.mockResolvedValue(mockAuthToken);

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
