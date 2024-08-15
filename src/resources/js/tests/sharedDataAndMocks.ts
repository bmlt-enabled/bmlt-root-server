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
import type { Token, ServiceBody, ServiceBodyCreate, ServiceBodyUpdate, User, UserCreate, UserUpdate } from 'bmlt-root-server-client';

import ApiClientWrapper from '../lib/RootServerApi';
import { apiCredentials, authenticatedUser } from '../stores/apiCredentials';
import App from '../App.svelte';
import runtime from '../../../node_modules/bmlt-root-server-client/dist/runtime';

type UserEventInstance = ReturnType<typeof userEvent.setup>;

export const mockServerAdmin: User = {
  description: 'Main Server Administrator',
  displayName: 'Server Administrator',
  email: 'mockadmin@bmlt.app',
  id: 1,
  ownerId: -1,
  type: 'admin',
  username: 'serveradmin'
};

export const mockNorthernZoneAdmin: User = {
  description: 'Northern Zone Administrator',
  displayName: 'Northern Zone',
  email: 'nzone@bmlt.app',
  id: 2,
  ownerId: -1,
  type: 'serviceBodyAdmin',
  username: 'NorthernZone'
};

export const mockBigRegionAdmin: User = {
  description: 'Big Region Administrator',
  displayName: 'Big Region',
  email: 'big@bmlt.app',
  id: 3,
  ownerId: 2,
  type: 'serviceBodyAdmin',
  username: 'BigRegion'
};

export const mockSmallRegionAdmin: User = {
  description: 'Small Region Administrator',
  displayName: 'Small Region',
  email: 'small@bmlt.app',
  id: 4,
  ownerId: -1,
  type: 'serviceBodyAdmin',
  username: 'SmallRegion'
};

export const mockRiverCityAreaAdmin: User = {
  description: 'River City Area Administrator',
  displayName: 'River City Area',
  email: 'river@bmlt.app',
  id: 5,
  ownerId: 3,
  type: 'serviceBodyAdmin',
  username: 'RiverCityArea'
};

export const mockMountainAreaAdmin: User = {
  description: 'Mountain Area Administrator',
  displayName: 'Mountain Area',
  email: 'mountain@bmlt.app',
  id: 6,
  ownerId: 3,
  type: 'serviceBodyAdmin',
  username: 'MountainArea'
};

export const mockRuralAreaAdmin: User = {
  description: 'Rural Area Administrator',
  displayName: 'Rural Area',
  email: 'rural@bmlt.app',
  id: 7,
  ownerId: 3,
  type: 'serviceBodyAdmin',
  username: 'RuralArea'
};

export const mockSmallRegionObserver: User = {
  description: 'Small Region Observer',
  displayName: 'Small Observer',
  email: 'smallobserver@bmlt.app',
  id: 8,
  ownerId: 2,
  type: 'observer',
  username: 'SmallObserver'
};

export const mockSmallRegionDeactivated: User = {
  description: 'Small Region Deactivated',
  displayName: 'Small Deactivated',
  email: 'smalldeactivated@bmlt.app',
  id: 9,
  ownerId: 2,
  type: 'deactivated',
  username: 'SmallDeactivated'
};

const mockNorthernZone: ServiceBody = {
  id: 1,
  name: 'Northern Zone',
  adminUserId: 1,
  type: 'ZF',
  parentId: null,
  assignedUserIds: [2, 3, 8],
  email: 'nzone@bmlt.app',
  description: 'Northern Zone Description',
  url: 'https://nzone.example.com',
  helpline: '123-456-7890',
  worldId: 'ZF123'
};

const mockBigRegion: ServiceBody = {
  id: 2,
  name: 'Big Region',
  adminUserId: 3,
  type: 'RG',
  parentId: 1,
  assignedUserIds: [2, 3],
  email: 'big@bmlt.app',
  description: 'Big Region Description',
  url: 'https://bigregion.example.com',
  helpline: '123-555-1212',
  worldId: 'RG125'
};

const mockSmallRegion: ServiceBody = {
  id: 3,
  name: 'Small Region',
  adminUserId: 2,
  type: 'RG',
  parentId: null,
  assignedUserIds: [2],
  email: 'small@bmlt.app',
  description: 'Small Region Description',
  url: 'https://smallregion.example.com',
  helpline: '555-867-5309',
  worldId: 'RG558'
};

const mockRiverCityArea: ServiceBody = {
  id: 4,
  name: 'River City Area',
  adminUserId: 2,
  type: 'AS',
  parentId: 2,
  assignedUserIds: [3, 2, 5],
  email: 'rivercity@bmlt.app',
  description: 'River City Area Description',
  url: 'https://rivercityarea.example.com',
  helpline: '803-555-1212',
  worldId: 'AS128'
};

const mockMountainArea: ServiceBody = {
  id: 5,
  name: 'Mountain Area',
  adminUserId: 2,
  type: 'AS',
  parentId: 2,
  assignedUserIds: [2, 6],
  email: 'mountain@bmlt.app',
  description: 'Mountain Area Description',
  url: 'https://mountainarea.example.com',
  helpline: '803-555-4242',
  worldId: 'AS428'
};

const mockRuralArea: ServiceBody = {
  id: 6,
  name: 'Rural Area',
  adminUserId: 7,
  type: 'AS',
  parentId: 2,
  assignedUserIds: [2, 7],
  email: 'rural@bmlt.app',
  description: 'Rural Area Description',
  url: 'https://ruralarea.example.com',
  helpline: '803-555-7247',
  worldId: 'AS778'
};

export const allUsers = [
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

export const allServiceBodies: ServiceBody[] = [mockNorthernZone, mockBigRegion, mockSmallRegion, mockRiverCityArea, mockMountainArea, mockRuralArea];

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

function userHasDependents(id: number): boolean {
  for (let i = 0; i < allUsersAndPasswords.length; i++) {
    if (allUsersAndPasswords[i].user.ownerId === id) {
      return true;
    }
  }
  for (let i = 0; i < allServiceBodies.length; i++) {
    if (allServiceBodies[i].assignedUserIds.includes(id)) {
      return true;
    }
  }
  return false;
}

function serviceBodyHasDependents(id: number): boolean {
  for (let i = 0; i < allServiceBodies.length; i++) {
    if (allServiceBodies[i].parentId === id) {
      return true;
    }
  }
  return false;
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

// mocks for editing, creating, and deleting a user
export let mockSavedUserCreate: UserCreate | null;
export let mockSavedUserUpdate: UserUpdate | null;
export let mockDeletedUserId: number | null = null;

async function mockCreateUser({ userCreate: user }: { userCreate: UserCreate }): Promise<User> {
  mockSavedUserCreate = user;
  return {
    username: user.username,
    type: user.type,
    displayName: user.displayName,
    description: user.description || '',
    email: user.email || '',
    ownerId: user.ownerId || -1,
    id: 42 // just make up an ID.  This assumes we aren't making more than one user in a test, or saving the new user between tests.
  };
}

// we aren't using the userId in the mock
// eslint-disable-next-line
async function mockUpdateUser({ userId: _, userUpdate: user }: { userId: number; userUpdate: UserUpdate }): Promise<void> {
  mockSavedUserUpdate = user;
}

async function mockDeleteUser({ userId: id }: { userId: number }): Promise<void> {
  if (userHasDependents(id)) {
    throw new ResponseError(makeResponse('Conflict', 409), 'Response returned an error code');
  }
  mockDeletedUserId = id;
}

// Service Body Mock Functions
async function mockGetServiceBody(params: { serviceBodyId: number }): Promise<ServiceBody> {
  const mockServiceBody = allServiceBodies.find((s) => s.id === params.serviceBodyId);
  if (mockServiceBody) {
    return mockServiceBody;
  }
  throw new Error('unknown service body -- something went wrong');
}

async function mockGetServiceBodies(): Promise<ServiceBody[]> {
  const userId = get(authenticatedUser)?.id;
  if (!userId) {
    throw new Error('internal error -- trying to get service bodies when no simulated user is logged in');
  } else {
    return [mockNorthernZone, mockBigRegion, mockSmallRegion, mockRiverCityArea, mockMountainArea, mockRuralArea];
  }
}

// mocks for editing, creating, and deleting a Service Body
export let mockSavedServiceBodyCreate: ServiceBodyCreate | null;
export let mockSavedServiceBodyUpdate: ServiceBodyUpdate | null;
export let mockDeletedServiceBodyId: number | null = null;

async function mockCreateServiceBody({ serviceBodyCreate: serviceBody }: { serviceBodyCreate: ServiceBodyCreate }): Promise<ServiceBody> {
  mockSavedServiceBodyCreate = serviceBody;
  return {
    adminUserId: serviceBody.adminUserId,
    type: serviceBody.type,
    parentId: serviceBody.parentId,
    assignedUserIds: serviceBody.assignedUserIds,
    name: serviceBody.name,
    email: serviceBody.email || '',
    description: serviceBody.description || '',
    url: serviceBody.url || '',
    helpline: serviceBody.helpline || '',
    worldId: serviceBody.worldId || '',
    id: 9
  };
}

// eslint-disable-next-line
async function mockUpdateServiceBody({ serviceBodyId: _, serviceBodyUpdate: serviceBody }: { serviceBodyId: number; serviceBodyUpdate: ServiceBodyUpdate }): Promise<void> {
  mockSavedServiceBodyUpdate = serviceBody;
}

async function mockDeleteServiceBody({ serviceBodyId: id }: { serviceBodyId: number }): Promise<void> {
  if (serviceBodyHasDependents(id)) {
    throw new ResponseError(makeResponse('Conflict', 409), 'Response returned an error code');
  }
  mockDeletedServiceBodyId = id;
}

export function sharedBeforeAll() {
  // set up mocks
  vi.spyOn(ApiClientWrapper.api, 'getUser').mockImplementation(mockGetUser);
  vi.spyOn(ApiClientWrapper.api, 'getUsers').mockImplementation(mockGetUsers);
  vi.spyOn(ApiClientWrapper.api, 'authToken').mockImplementation(mockAuthToken);
  vi.spyOn(ApiClientWrapper.api, 'authRefresh').mockImplementation(mockAuthRefresh);
  vi.spyOn(ApiClientWrapper.api, 'authLogout').mockImplementation(mockAuthLogout);
  vi.spyOn(ApiClientWrapper.api, 'createUser').mockImplementation(mockCreateUser);
  vi.spyOn(ApiClientWrapper.api, 'updateUser').mockImplementation(mockUpdateUser);
  vi.spyOn(ApiClientWrapper.api, 'deleteUser').mockImplementation(mockDeleteUser);
  vi.spyOn(ApiClientWrapper.api, 'getServiceBody').mockImplementation(mockGetServiceBody);
  vi.spyOn(ApiClientWrapper.api, 'getServiceBodies').mockImplementation(mockGetServiceBodies);
  vi.spyOn(ApiClientWrapper.api, 'createServiceBody').mockImplementation(mockCreateServiceBody);
  vi.spyOn(ApiClientWrapper.api, 'updateServiceBody').mockImplementation(mockUpdateServiceBody);
  vi.spyOn(ApiClientWrapper.api, 'deleteServiceBody').mockImplementation(mockDeleteServiceBody);
}

export function sharedBeforeEach() {
  mockSavedUserCreate = null;
  mockSavedUserUpdate = null;
  mockDeletedUserId = null;

  mockSavedServiceBodyCreate = null;
  mockSavedServiceBodyUpdate = null;
  mockDeletedServiceBodyId = null;
}

export async function sharedAfterEach() {
  // clean up - don't leave in a logged in state (will be a no-op if we aren't logged in at this point)
  replace('/');
  await apiCredentials.logout();
}

// utility function to log in as a specific user, and optionally open the provided tab
export async function login(username: string, tab: string | null = null): Promise<UserEventInstance> {
  const user = userEvent.setup();
  render(App);
  await user.type(await screen.findByRole('textbox', { name: 'Username' }), username);
  await user.type(await screen.findByLabelText('Password'), findPassword(username));
  await user.click(await screen.findByRole('button', { name: 'Log In' }));
  if (tab) {
    const link = await screen.findByRole('link', { name: tab, hidden: true });
    await user.click(link);
  }
  return user;
}
