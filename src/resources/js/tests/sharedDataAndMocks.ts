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
      Small Region Deactivated
Server Administrator is a server admin and the next 6 are service body admins.  Small Region Observer
is an observer.  Small Region Deactivated is a deactivated user.
*/

import { get } from 'svelte/store';
import { render, screen } from '@testing-library/svelte';
import { replace } from 'svelte-spa-router';
import userEvent from '@testing-library/user-event';
import { vi } from 'vitest';

import { ResponseError } from 'bmlt-root-server-client';
import type { Token, User } from 'bmlt-root-server-client';

import ApiClientWrapper from '../lib/RootServerApi';
import { apiCredentials, authenticatedUser } from '../stores/apiCredentials';
import App from '../App.svelte';
import runtime from '../../../node_modules/bmlt-root-server-client/dist/runtime';

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

const mockSmallRegionDeactivated: User = {
  description: 'Small Region Deactivated',
  displayName: 'Small Deactivated',
  email: 'smalldeactivated@bmlt.app',
  id: 9,
  ownerId: 2,
  type: 'deactivated',
  username: 'SmallDeactivated'
};

const allUsersAndPasswords = [
  { user: mockServerAdmin, password: 'serveradmin-password' },
  { user: mockNorthernZoneAdmin, password: 'northern-zone-password' },
  { user: mockBigRegionAdmin, password: 'big-region-password' },
  { user: mockSmallRegionAdmin, password: 'small-region-password' },
  { user: mockRiverCityAreaAdmin, password: 'river-city-area-password' },
  { user: mockMountainAreaAdmin, password: 'mountain-area-password' },
  { user: mockRuralAreaAdmin, password: 'rural-area-password' },
  { user: mockSmallRegionObserver, password: 'small-region-observer-password' },
  { user: mockSmallRegionDeactivated, password: 'small-region-deactivated-password' }
];

function findPassword(name: string): string {
  for (let i = 0; i < allUsersAndPasswords.length; i++) {
    if (allUsersAndPasswords[i].user.username === name) {
      return allUsersAndPasswords[i].password;
    }
  }
  throw new Error('internal testing error - could not find password for given username');
}

// define a mock authToken that expires 24 hours from now
// to trigger a call to authRefresh, set the token to expire in 1 hour instead
function generateMockAuthToken(userId: number): Token {
  // the token uses PHP's time rather than Javascript's time, so seconds from the epoch instead of milliseconds
  const now: number = Math.round(new Date().valueOf() / 1000);
  return {
    accessToken: 'mysteryString42',
    expiresAt: now + 60 * 60 * 24,
    tokenType: 'bearer',
    userId: userId
  };
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
  const userId = get(authenticatedUser)?.id;
  if (!userId) {
    throw new Error('internal error -- trying to get users when no simulated user is logged in');
  } else if (userId === mockServerAdmin.id) {
    return [
      mockServerAdmin,
      mockNorthernZoneAdmin,
      mockBigRegionAdmin,
      mockSmallRegionAdmin,
      mockRiverCityAreaAdmin,
      mockMountainAreaAdmin,
      mockRuralAreaAdmin,
      mockSmallRegionObserver,
      mockSmallRegionDeactivated
    ];
  } else if (userId === mockNorthernZoneAdmin.id) {
    return [mockNorthernZoneAdmin, mockBigRegionAdmin, mockSmallRegionAdmin, mockSmallRegionObserver, mockSmallRegionDeactivated];
  } else if (userId === mockBigRegionAdmin.id) {
    return [mockBigRegionAdmin, mockRiverCityAreaAdmin, mockMountainAreaAdmin, mockRuralAreaAdmin];
  } else if (userId === mockSmallRegionAdmin.id) {
    return [mockSmallRegionAdmin];
  } else if (userId === mockRiverCityAreaAdmin.id) {
    return [mockRiverCityAreaAdmin];
  } else if (userId === mockMountainAreaAdmin.id) {
    return [mockMountainAreaAdmin];
  } else if (userId === mockRuralAreaAdmin.id) {
    return [mockRuralAreaAdmin];
  } else if (userId === mockSmallRegionObserver.id) {
    return [mockSmallRegionObserver];
  } else if (userId === mockSmallRegionDeactivated.id) {
    return [mockSmallRegionDeactivated];
  } else {
    throw new Error('internal error -- user ID not found in mockGetUsers');
  }
}

function makeResponse(msg: string, status: number): Response {
  const m = `{ "message": "${msg}" }`;
  const unicodeMsg = Uint8Array.from(Array.from(m).map((x) => x.charCodeAt(0)));
  const strm = new ReadableStream({
    start(controller) {
      controller.enqueue(unicodeMsg);
      controller.close();
    }
  });
  return new Response(strm, { status: status, statusText: 'Unauthorized' });
}

async function mockAuthToken(authTokenRequest: { tokenCredentials: { username: string; password: string } }): Promise<Token> {
  const n = authTokenRequest.tokenCredentials.username;
  const p = authTokenRequest.tokenCredentials.password;
  for (let i = 0; i < allUsersAndPasswords.length; i++) {
    if (allUsersAndPasswords[i].user.username === n && allUsersAndPasswords[i].password === p) {
      if (allUsersAndPasswords[i].user.type === 'deactivated') {
        throw new ResponseError(makeResponse('User is deactivated.', 403), 'Response returned an error code');
      } else {
        return generateMockAuthToken(allUsersAndPasswords[i].user.id);
      }
    }
  }
  throw new ResponseError(makeResponse('The provided credentials are incorrect.', 401), 'Response returned an error code');
}

async function mockAuthRefresh(): Promise<Token> {
  const userId = get(authenticatedUser)?.id;
  if (userId) {
    return generateMockAuthToken(userId);
  } else {
    throw new Error('internal error -- authenticated user not found');
  }
}

async function mockAuthLogout(): Promise<void> {
  // Nothing to do here!
  // For mocking authentication we just rely on the authenticatedUser in the ApiCredentialsStore
}

export function setupMocks() {
  vi.spyOn(ApiClientWrapper.api, 'getUser').mockImplementation(mockGetUser);
  vi.spyOn(ApiClientWrapper.api, 'getUsers').mockImplementation(mockGetUsers);
  vi.spyOn(ApiClientWrapper.api, 'authToken').mockImplementation(mockAuthToken);
  vi.spyOn(ApiClientWrapper.api, 'authRefresh').mockImplementation(mockAuthRefresh);
  vi.spyOn(ApiClientWrapper.api, 'authLogout').mockImplementation(mockAuthLogout);
}

export async function sharedAfterEach() {
  // clean up - don't leave in a logged in state (will be a no-op if we aren't logged in at this point)
  replace('/');
  await apiCredentials.logout();
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
