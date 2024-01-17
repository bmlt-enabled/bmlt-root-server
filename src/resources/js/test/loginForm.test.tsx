/* Unit tests for the initial login screen, including the language selection menu when enabled */
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

const mockObserver: User = {
  description: 'River City Area Observer',
  displayName: 'River Observer',
  email: 'nobody@bmlt.app',
  id: 7,
  ownerId: '5',
  type: 'observer',
  username: 'RiverObserver',
};

const allMockUsers = [mockServerAdmin, mockAreaAdmin, mockObserver];
const allMockPasswords = ['serveradmin-password', 'rivercity-password', 'river-observer-password'];

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

async function mockGetUsers(_?: any): Promise<User[]> {
  if (!savedAccessToken) {
    throw new Error('internal error -- trying to get users when no simulated user is logged in');
  } else if (savedAccessToken.userId === mockServerAdmin.id) {
    return [mockServerAdmin, mockAreaAdmin, mockObserver];
  } else {
    throw new Error('internal error -- user ID not handled by this mock');
  }
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
  vi.spyOn(ApiClientWrapper.api, 'getUsers').mockImplementation(mockGetUsers);
  vi.spyOn(ApiClientWrapper.api, 'authToken').mockImplementation(mockAuthToken);
  vi.spyOn(ApiClientWrapper.api, 'authLogout').mockImplementation(mockAuthLogout);
});

beforeEach(async () => {
  savedAccessToken = null;
});

// One of the language selection tests enables the language selector.  Make sure it is reset after any test.
afterEach(async () => {
  const settings = (global as any).settings;
  settings['isLanguageSelectorEnabled'] = false;
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

  test('log in with valid username and password for an observer', async () => {
    const user = userEvent.setup();
    render(<App />);
    await user.type(screen.getByRole('textbox', { name: 'Username' }), 'RiverObserver');
    await user.type(screen.getByLabelText(/password/i), 'river-observer-password');
    await user.click(screen.getByRole('button', { name: 'Log In' }));
    // after a successful login, we should see the dashboard, including the word 'Dashboard' and the user name
    await screen.findByText(/Dashboard/);
    await screen.findByText(/River Observer/);
  });
});

describe('language selection tests', () => {
  test('check default (language selection menu not shown)', async () => {
    render(<App />);
    const lang = screen.queryByLabelText('Select Language');
    expect(lang).toBeNull();
  });

  // Check that language selection menu is shown when it is enabled and that it works, by selecting German.
  // This test goes a little further than the others in this file, in that it mocks clicking on one of links
  // ('Users'), and making sure that part is still in German.  The bulk of the tests of the 'Users' page are in a
  // separate test file though.
  test('check language selection menu', async () => {
    const settings = (global as any).settings;
    settings['isLanguageSelectorEnabled'] = true;
    settings['languageMapping'] = { de: 'Deutsch', en: 'English' };
    const user = userEvent.setup();
    render(<App />);
    // we're still using English here, so the 'Select Language' label is in English
    const lang = await screen.findByLabelText('Select Language');
    await user.click(lang);
    const de = await screen.findByRole('option', { name: 'Deutsch' });
    await user.click(de);
    // login screen should now be in German
    await user.type(screen.getByRole('textbox', { name: 'Benutzername' }), 'serveradmin'); // Benutzername = Username
    await user.type(screen.getByLabelText(/passwort/i), 'serveradmin-password'); // Passwort = Password
    await user.click(screen.getByRole('button', { name: 'Anmelden' })); // Anmelden = Log In
    // after a successful login, we should see the dashboard, including a link to the Users page
    // (which will be called Benutzer in German)
    const link = await screen.findByRole('link', { name: 'Benutzer' });
    await user.click(link);
    // we should see the default 'Create New User' even without clicking on the dropdown (Erstelle einen neuen Benutzer in German)
    await screen.findByRole('button', { name: /erstelle einen neuen benutzer/i });
    // Log out, and make sure we're back at the login screen.  Now it should be entirely in German, including the
    // language selection menu label.
    await user.click(screen.getByRole('button', { name: 'Abmelden' })); // Abmelden = Sign Out
    // Anmeldung = Login (this is loginTitle, not loginVerb)
    expect(screen.getByRole('heading', { level: 3 })).toHaveTextContent('Anmeldung'); // Anmeldung = Log In
    expect(screen.getByLabelText('Sprache auswählen')).toBeInTheDocument(); // Sprache auswählen = Select Language
  });

  // Caution: state.language is memoized, and changing settings['defaultLanguage'] in a test doesn't get picked up.
  // You can change it in setup.ts, to mock having it in the auto config file, but that changes the default language
  // to German for *all* the tests.  So if you add any additional tests for non-English languages, the easiest path
  // is to select the other language explicitly from the language selection menu (as in the above test).
});

afterAll(async () => {
  vi.restoreAllMocks();
});
