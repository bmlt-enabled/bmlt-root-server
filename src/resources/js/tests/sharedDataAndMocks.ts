/* Shared sample data and mocks for UI unit tests.

Mock users as follows:
  Server Administrator
    Northern Zone
      Big Region
        River City Area
        Mountain Area
        Rural Area
      Small Region
      Small Region Observer
Server Administrator is a server admin, the next 6 are service body admins, and Small Region Observer
is an observer.
*/

import { render, screen } from '@testing-library/svelte';
import userEvent from '@testing-library/user-event';
import { vi } from 'vitest';
import App from '../App.svelte';
import { ResponseError } from 'bmlt-root-server-client';
import type { Token, User } from 'bmlt-root-server-client';
import ApiClientWrapper from '../lib/RootServerApi';
import runtime from '../../../node_modules/bmlt-root-server-client/dist/runtime';
import { apiCredentials } from '../stores/apiCredentials';

const mockServerAdmin: User = {
  description: 'Main Server Administrator',
  displayName: 'Server Administrator',
  email: 'mockadmin@bmlt.app',
  id: 1,
  ownerId: -1,
  type: 'admin',
  username: 'serveradmin'
};

const mockNorthernZoneAdmin: User = {
  description: 'Northern Zone Administrator',
  displayName: 'Northern Zone',
  email: 'nzone@bmlt.app',
  id: 2,
  ownerId: -1,
  type: 'serviceBodyAdmin',
  username: 'NorthernZone'
};

const mockBigRegionAdmin: User = {
  description: 'Big Region Administrator',
  displayName: 'Big Region',
  email: 'big@bmlt.app',
  id: 3,
  ownerId: 2,
  type: 'serviceBodyAdmin',
  username: 'BigRegion'
};

const mockSmallRegionAdmin: User = {
  description: 'Small Region Administrator',
  displayName: 'Small Region',
  email: 'small@bmlt.app',
  id: 4,
  ownerId: 2,
  type: 'serviceBodyAdmin',
  username: 'SmallRegion'
};

const mockRiverCityAreaAdmin: User = {
  description: 'River City Area Administrator',
  displayName: 'River City Area',
  email: 'river@bmlt.app',
  id: 5,
  ownerId: 3,
  type: 'serviceBodyAdmin',
  username: 'RiverCityArea'
};

const mockMountainAreaAdmin: User = {
  description: 'Mountain Area Administrator',
  displayName: 'Mountain Area',
  email: 'mountain@bmlt.app',
  id: 6,
  ownerId: 3,
  type: 'serviceBodyAdmin',
  username: 'MountainArea'
};

const mockRuralAreaAdmin: User = {
  description: 'Rural Area Administrator',
  displayName: 'Rural Area',
  email: 'rural@bmlt.app',
  id: 7,
  ownerId: 3,
  type: 'serviceBodyAdmin',
  username: 'RuralArea'
};

const mockSmallRegionObserver: User = {
  description: 'Small Region Observer',
  displayName: 'Small Observer',
  email: 'smallobserver@bmlt.app',
  id: 8,
  ownerId: 2,
  type: 'observer',
  username: 'SmallObserver'
};

const allUsersAndPasswords = [
  { user: mockServerAdmin, password: 'serveradmin-password' },
  { user: mockNorthernZoneAdmin, password: 'northern-zone-password' },
  { user: mockBigRegionAdmin, password: 'big-region-password' },
  { user: mockSmallRegionAdmin, password: 'small-region-password' },
  { user: mockRiverCityAreaAdmin, password: 'river-city-area-password' },
  { user: mockMountainAreaAdmin, password: 'mountain-area-password' },
  { user: mockRuralAreaAdmin, password: 'rural-area-password' },
  { user: mockSmallRegionObserver, password: 'small-region-observer-password' }
];

function findPassword(name: string): string {
  for (let i = 0; i < allUsersAndPasswords.length; i++) {
    if (allUsersAndPasswords[i].user.username === name) {
      return allUsersAndPasswords[i].password;
    }
  }
  throw new Error('internal testing error - could not find password for given username');
}

// mocked access token
let mockSavedAccessToken: Token | null;

// define a mock authToken that expires 1 hour from now
function generateMockToken(u: User): Token {
  // the token uses PHP's time rather than Javascript's time, so seconds from the epoch instead of milliseconds
  const now: number = Math.round(new Date().valueOf() / 1000);
  return {
    accessToken: 'mysteryString42',
    expiresAt: now + 60 * 60,
    tokenType: 'bearer',
    userId: u.id
  };
}

function mockGetToken(): Token | null {
  return mockSavedAccessToken;
}

function mockSetToken(token: Token | null): void {
  mockSavedAccessToken = token;
}

async function mockGetUser(params: { userId: number }): Promise<User> {
  const mockUser = allUsersAndPasswords.find((u) => u.user.id === params.userId);
  if (mockUser) {
    return mockUser.user;
  }
  throw new Error('unknown user -- something went wrong');
}

// The value of initOverrides is not used in the mock, so tell the linter it's ok.
// eslint-disable-next-line
async function mockGetUsers(initOverrides?: RequestInit | runtime.InitOverrideFunction): Promise<User[]> {
  if (!mockSavedAccessToken) {
    throw new Error('internal error -- trying to get users when no simulated user is logged in');
  } else if (mockSavedAccessToken.userId === mockServerAdmin.id) {
    return [mockServerAdmin, mockNorthernZoneAdmin, mockBigRegionAdmin, mockSmallRegionAdmin, mockRiverCityAreaAdmin, mockMountainAreaAdmin, mockRuralAreaAdmin, mockSmallRegionObserver];
  } else if (mockSavedAccessToken.userId === mockNorthernZoneAdmin.id) {
    return [mockNorthernZoneAdmin, mockBigRegionAdmin, mockSmallRegionAdmin, mockSmallRegionObserver];
  } else if (mockSavedAccessToken.userId === mockBigRegionAdmin.id) {
    return [mockBigRegionAdmin, mockRiverCityAreaAdmin, mockMountainAreaAdmin, mockRuralAreaAdmin];
  } else if (mockSavedAccessToken.userId === mockSmallRegionAdmin.id) {
    return [mockSmallRegionAdmin];
  } else if (mockSavedAccessToken.userId === mockRiverCityAreaAdmin.id) {
    return [mockRiverCityAreaAdmin];
  } else if (mockSavedAccessToken.userId === mockMountainAreaAdmin.id) {
    return [mockMountainAreaAdmin];
  } else if (mockSavedAccessToken.userId === mockRuralAreaAdmin.id) {
    return [mockRuralAreaAdmin];
  } else if (mockSavedAccessToken.userId === mockSmallRegionObserver.id) {
    return [mockSmallRegionObserver];
  } else {
    throw new Error('internal error -- user ID not found in mockGetUsers');
  }
}

function mockIsLoggedIn(): boolean {
  return Boolean(mockSavedAccessToken);
}

async function mockAuthToken(authTokenRequest: { tokenCredentials: { username: string; password: string } }): Promise<Token> {
  const n = authTokenRequest.tokenCredentials.username;
  const p = authTokenRequest.tokenCredentials.password;
  for (let i = 0; i < allUsersAndPasswords.length; i++) {
    if (allUsersAndPasswords[i].user.username === n && allUsersAndPasswords[i].password === p) {
      return generateMockToken(allUsersAndPasswords[i].user);
    }
  }
  const msg = '{ "message": "The provided credentials are incorrect." }';
  const unicodeMsg = Uint8Array.from(Array.from(msg).map((x) => x.charCodeAt(0)));
  const strm = new ReadableStream({
    start(controller) {
      controller.enqueue(unicodeMsg);
      controller.close();
    }
  });
  const r = new Response(strm, { status: 401, statusText: 'Unauthorized' });
  throw new ResponseError(r, 'Response returned an error code');
}

async function mockAuthLogout(): Promise<void> {
  mockSavedAccessToken = null;
}

export function setupMocks() {
  vi.spyOn(ApiClientWrapper.api, 'token', 'get').mockImplementation(mockGetToken);
  vi.spyOn(ApiClientWrapper.api, 'token', 'set').mockImplementation(mockSetToken);
  vi.spyOn(ApiClientWrapper.api, 'isLoggedIn', 'get').mockImplementation(mockIsLoggedIn);
  vi.spyOn(ApiClientWrapper.api, 'getUser').mockImplementation(mockGetUser);
  vi.spyOn(ApiClientWrapper.api, 'getUsers').mockImplementation(mockGetUsers);
  vi.spyOn(ApiClientWrapper.api, 'authToken').mockImplementation(mockAuthToken);
  vi.spyOn(ApiClientWrapper.api, 'authLogout').mockImplementation(mockAuthLogout);
}

export function sharedBeforeEach() {
  // TODO: fix this.  Hack for now -- make sure we weren't left in a logged in state
  apiCredentials.logout();
  mockSavedAccessToken = null;
}

// utility function to log in as a specific user, and open the provided tab
// TODO: not sure how to declare return type.  Thought it should be Promise<UserEvent> but that doesn't work.
export async function loginAndOpenTab(username: string, tab: string): Promise<any> {
  const user = userEvent.setup();
  render(App);
  await user.type(await screen.findByRole('textbox', { name: 'Username' }), username);
  await user.type(await screen.findByLabelText('Password'), findPassword(username));
  await user.click(await screen.findByRole('button', { name: 'Log In' }));
  const link = await screen.findByRole('link', { name: tab, hidden: true });
  await user.click(link);
  return user;
}
