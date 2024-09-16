/* Shared sample data and mocks for UI unit tests.

Mock service bodies are as follows:
  Northern Zone
    Big Region
      River City Area
      Mountain Area
      Rural Area
    Small Region

Mock users as follows.  There is a serveradmin:
  Server Administrator

Here are the admins for the above service bodies:
  Northern Zone Administrator
    Big Region Administrator
      River City Area Administrator
      Mountain Area Administrator
      Rural Area Administrator
    Small Region Administrator

There are a couple of extra service body admins to test other functionality:
  Big Region Admin 2 (owned by serveradmin rather than Northern Zone Admin, to test content for Northern Zone Admin)
  Rural Area Admin 2 (owned by Big Region Admin)

And an observer and a deactivated user:
  Small Region Observer (owned by Northern Zone Admin)
  Small Region Deactivated (owned by Northern Zone Admin)

*/

import { get } from 'svelte/store';
import { render, screen } from '@testing-library/svelte';
import { replace } from 'svelte-spa-router';
import userEvent from '@testing-library/user-event';
import { vi } from 'vitest';

import { ResponseError } from 'bmlt-root-server-client';
import type {
  Meeting,
  Format,
  FormatCreate,
  FormatUpdate,
  MeetingCreate,
  MeetingUpdate,
  ServiceBody,
  ServiceBodyCreate,
  ServiceBodyUpdate,
  Token,
  User,
  UserCreate,
  UserPartialUpdate,
  UserUpdate
} from 'bmlt-root-server-client';

import ApiClientWrapper from '../lib/RootServerApi';
import { apiCredentials, authenticatedUser } from '../stores/apiCredentials';
import App from '../App.svelte';
import runtime from '../../../node_modules/bmlt-root-server-client/dist/runtime';

type UserEventInstance = ReturnType<typeof userEvent.setup>;

export const serverAdmin: User = {
  description: 'Main Server Administrator',
  displayName: 'Server Administrator',
  email: 'mockadmin@bmlt.app',
  id: 1,
  ownerId: -1,
  type: 'admin',
  username: 'serveradmin'
};

export const northernZoneAdmin: User = {
  description: 'Northern Zone Administrator',
  displayName: 'Northern Zone',
  email: 'nzone@bmlt.app',
  id: 2,
  ownerId: -1,
  type: 'serviceBodyAdmin',
  username: 'NorthernZone'
};

export const bigRegionAdmin: User = {
  description: 'Big Region Administrator',
  displayName: 'Big Region',
  email: 'big@bmlt.app',
  id: 3,
  ownerId: northernZoneAdmin.id,
  type: 'serviceBodyAdmin',
  username: 'BigRegion'
};

export const smallRegionAdmin: User = {
  description: 'Small Region Administrator',
  displayName: 'Small Region',
  email: 'small@bmlt.app',
  id: 4,
  ownerId: northernZoneAdmin.id,
  type: 'serviceBodyAdmin',
  username: 'SmallRegion'
};

export const riverCityAreaAdmin: User = {
  description: 'River City Area Administrator',
  displayName: 'River City Area',
  email: 'river@bmlt.app',
  id: 5,
  ownerId: bigRegionAdmin.id,
  type: 'serviceBodyAdmin',
  username: 'RiverCityArea'
};

export const mountainAreaAdmin: User = {
  description: 'Mountain Area Administrator',
  displayName: 'Mountain Area',
  email: 'mountain@bmlt.app',
  id: 6,
  ownerId: bigRegionAdmin.id,
  type: 'serviceBodyAdmin',
  username: 'MountainArea'
};

export const ruralAreaAdmin: User = {
  description: 'Rural Area Administrator',
  displayName: 'Rural Area',
  email: 'rural@bmlt.app',
  id: 7,
  ownerId: bigRegionAdmin.id,
  type: 'serviceBodyAdmin',
  username: 'RuralArea'
};

export const smallRegionObserver: User = {
  description: 'Small Region Observer',
  displayName: 'Small Observer',
  email: 'smallobserver@bmlt.app',
  id: 8,
  ownerId: northernZoneAdmin.id,
  type: 'observer',
  username: 'SmallObserver'
};

export const smallRegionDeactivated: User = {
  description: 'Small Region Deactivated',
  displayName: 'Small Deactivated',
  email: 'smalldeactivated@bmlt.app',
  id: 9,
  ownerId: northernZoneAdmin.id,
  type: 'deactivated',
  username: 'SmallDeactivated'
};

export const bigRegionAdmin2: User = {
  description: 'Big Region Second Administrator',
  displayName: 'Big Region Admin 2',
  email: 'big2@bmlt.app',
  id: 10,
  ownerId: -1,
  type: 'serviceBodyAdmin',
  username: 'BigRegion2'
};

export const ruralAreaAdmin2: User = {
  description: 'Rural Area Second Administrator',
  displayName: 'Rural Area Admin 2',
  email: 'rural2@bmlt.app',
  id: 11,
  ownerId: bigRegionAdmin.id,
  type: 'serviceBodyAdmin',
  username: 'RuralArea2'
};

export const northernZone: ServiceBody = {
  id: 101,
  name: 'Northern Zone',
  adminUserId: northernZoneAdmin.id,
  type: 'ZF',
  parentId: null,
  assignedUserIds: [],
  email: 'nzone@bmlt.app',
  description: 'Northern Zone Description',
  url: 'https://nzone.example.com',
  helpline: '123-456-7890',
  worldId: 'ZF123'
};

export const bigRegion: ServiceBody = {
  id: 102,
  name: 'Big Region',
  adminUserId: bigRegionAdmin.id,
  type: 'RG',
  parentId: northernZone.id,
  assignedUserIds: [bigRegionAdmin2.id],
  email: 'big@bmlt.app',
  description: 'Big Region Description',
  url: 'https://bigregion.example.com',
  helpline: '123-555-1212',
  worldId: 'RG125'
};

export const smallRegion: ServiceBody = {
  id: 103,
  name: 'Small Region',
  adminUserId: smallRegionAdmin.id,
  type: 'RG',
  parentId: northernZone.id,
  assignedUserIds: [smallRegionObserver.id, smallRegionDeactivated.id],
  email: 'small@bmlt.app',
  description: 'Small Region Description',
  url: 'https://smallregion.example.com',
  helpline: '555-867-5309',
  worldId: 'RG558'
};

export const riverCityArea: ServiceBody = {
  id: 104,
  name: 'River City Area',
  adminUserId: riverCityAreaAdmin.id,
  type: 'AS',
  parentId: bigRegion.id,
  assignedUserIds: [],
  email: 'rivercity@bmlt.app',
  description: 'River City Area Description',
  url: 'https://rivercityarea.example.com',
  helpline: '803-555-1212',
  worldId: 'AS128'
};

export const mountainArea: ServiceBody = {
  id: 105,
  name: 'Mountain Area',
  adminUserId: mountainAreaAdmin.id,
  type: 'AS',
  parentId: bigRegion.id,
  assignedUserIds: [],
  email: 'mountain@bmlt.app',
  description: 'Mountain Area Description',
  url: 'https://mountainarea.example.com',
  helpline: '803-555-4242',
  worldId: 'AS428'
};

export const ruralArea: ServiceBody = {
  id: 106,
  name: 'Rural Area',
  adminUserId: ruralAreaAdmin.id,
  type: 'AS',
  parentId: bigRegion.id,
  assignedUserIds: [ruralAreaAdmin2.id],
  email: 'rural@bmlt.app',
  description: 'Rural Area Description',
  url: 'https://ruralarea.example.com',
  helpline: '803-555-7247',
  worldId: 'AS778'
};

export const closedFormat: Format = {
  id: 4,
  translations: [
    { key: 'C', language: 'en', name: 'Closed', description: 'This meeting is closed to non-addicts. You should attend only if you believe that you may have a problem with substance abuse.' },
    { key: 'C', language: 'es', name: 'Cerrado', description: 'Esta reunión está cerrada a los no adictos. Usted debe asistir solamente si cree que puede tener un problema con abuso de drogas.' }
  ],
  worldId: 'CLOSED',
  type: 'OPEN_OR_CLOSED'
};

export const openFormat: Format = {
  id: 17,
  translations: [
    { key: 'O', language: 'en', name: 'Open', description: 'This meeting is open to addicts and non-addicts alike. All are welcome.' },
    { key: 'O', language: 'es', name: 'Abierta', description: 'Esta reunión está abierta a los adictos y a los no adictos por igual. Todos son bienvenidos.' }
  ],
  worldId: 'OPEN',
  type: 'OPEN_OR_CLOSED'
};

export const discussionFormat: Format = {
  id: 22,
  translations: [
    { key: 'D', language: 'en', name: 'Discussion', description: 'This meeting invites participation by all attendees.' },
    { key: 'D', language: 'es', name: 'Discusión', description: 'Esta reunión invita la participación de todos los asistentes.' }
  ],
  worldId: 'DISC',
  type: 'MEETING_FORMAT'
};

export const basicTextFormat: Format = {
  id: 19,
  translations: [
    { key: 'BT', language: 'en', name: 'Basic Text', description: 'This meeting is focused on discussion of the Basic Text of Narcotics Anonymous.' },
    { key: 'BT', language: 'es', name: 'Texto Básico', description: 'Esta reunión se centra en la discusión del texto básico de Narcóticos Anónimos.' }
  ],
  worldId: 'BT',
  type: 'MEETING_FORMAT'
};

export const jtFormat: Format = {
  id: 21,
  translations: [
    { key: 'JT', language: 'en', name: '"Just for Today"', description: 'This meeting is focused on discussion of the Just For Today text.' },
    { key: 'JT', language: 'es', name: 'Solo por Hoy', description: 'Esta reunión se centra en la discusión del texto Solo por Hoy.' }
  ],
  worldId: 'JFT',
  type: 'MEETING_FORMAT'
};

export const ruralMeeting: Meeting = {
  busLines: undefined,
  comments: undefined,
  contactEmail1: undefined,
  contactEmail2: undefined,
  contactName1: undefined,
  contactName2: undefined,
  contactPhone1: undefined,
  contactPhone2: undefined,
  day: 4,
  duration: '01:00',
  email: '',
  formatIds: [closedFormat.id, discussionFormat.id],
  id: 1061,
  latitude: 41.088456,
  locationCitySubsection: undefined,
  locationInfo: undefined,
  locationMunicipality: 'Nyack',
  locationNation: 'USA',
  locationNeighborhood: undefined,
  locationPostalCode1: '10960',
  locationProvince: 'NY',
  locationStreet: '67 S. Rural Dr',
  locationSubProvince: 'Rockland',
  locationText: 'The Rural Church',
  longitude: -73.918978,
  name: 'Real Talk',
  phoneMeetingNumber: undefined,
  published: true,
  serviceBodyId: ruralArea.id,
  startTime: '19:30',
  temporarilyVirtual: false,
  timeZone: 'America/New_York',
  trainLines: undefined,
  venueType: 1,
  virtualMeetingAdditionalInfo: undefined,
  virtualMeetingLink: undefined,
  worldId: 'G00212222'
};

export const mountainMeeting: Meeting = {
  busLines: undefined,
  comments: undefined,
  contactEmail1: undefined,
  contactEmail2: undefined,
  contactName1: undefined,
  contactName2: undefined,
  contactPhone1: undefined,
  contactPhone2: undefined,
  day: 6,
  duration: '01:00',
  email: '',
  formatIds: [openFormat.id],
  id: 1051,
  latitude: 39.4817,
  locationCitySubsection: undefined,
  locationInfo: undefined,
  locationMunicipality: 'Breckenridge',
  locationNation: 'USA',
  locationNeighborhood: undefined,
  locationPostalCode1: '80014',
  locationProvince: 'CO',
  locationStreet: '123 Summit Rd',
  locationSubProvince: 'Summit',
  locationText: 'The Mountain Club',
  longitude: -106.0384,
  name: 'Mountain Meeting',
  phoneMeetingNumber: undefined,
  published: true,
  serviceBodyId: mountainArea.id,
  startTime: '19:00',
  temporarilyVirtual: false,
  timeZone: 'America/Denver',
  trainLines: undefined,
  venueType: 1,
  virtualMeetingAdditionalInfo: undefined,
  virtualMeetingLink: undefined,
  worldId: 'G00222222'
};

export const riverCityMeeting: Meeting = {
  busLines: undefined,
  comments: undefined,
  contactEmail1: undefined,
  contactEmail2: undefined,
  contactName1: undefined,
  contactName2: undefined,
  contactPhone1: undefined,
  contactPhone2: undefined,
  day: 2,
  duration: '01:30',
  email: '',
  formatIds: [openFormat.id, basicTextFormat.id],
  id: 1041,
  latitude: 34.123456,
  locationCitySubsection: undefined,
  locationInfo: undefined,
  locationMunicipality: 'River City',
  locationNation: 'USA',
  locationNeighborhood: undefined,
  locationPostalCode1: '29201',
  locationProvince: 'SC',
  locationStreet: '789 River St',
  locationSubProvince: 'Richland',
  locationText: 'River Community Center',
  longitude: -81.0346,
  name: 'River Reflections',
  phoneMeetingNumber: undefined,
  published: true,
  serviceBodyId: riverCityArea.id,
  startTime: '18:00',
  temporarilyVirtual: false,
  timeZone: 'America/New_York',
  trainLines: undefined,
  venueType: 1,
  virtualMeetingAdditionalInfo: undefined,
  virtualMeetingLink: undefined,
  worldId: 'G00333333'
};

export const smallRegionMeeting: Meeting = {
  busLines: undefined,
  comments: undefined,
  contactEmail1: undefined,
  contactEmail2: undefined,
  contactName1: undefined,
  contactName2: undefined,
  contactPhone1: undefined,
  contactPhone2: undefined,
  day: 1,
  duration: '01:15',
  email: '',
  formatIds: [closedFormat.id, jtFormat.id],
  id: 1031,
  latitude: 38.89511,
  locationCitySubsection: undefined,
  locationInfo: undefined,
  locationMunicipality: 'Smallville',
  locationNation: 'USA',
  locationNeighborhood: undefined,
  locationPostalCode1: '20500',
  locationProvince: 'DC',
  locationStreet: '1600 Small St NW',
  locationSubProvince: undefined,
  locationText: 'Smallville Hall',
  longitude: -77.03637,
  name: 'Small Beginnings',
  phoneMeetingNumber: undefined,
  published: true,
  serviceBodyId: smallRegion.id,
  startTime: '17:45',
  temporarilyVirtual: false,
  timeZone: 'America/New_York',
  trainLines: undefined,
  venueType: 1,
  virtualMeetingAdditionalInfo: undefined,
  virtualMeetingLink: undefined,
  worldId: 'G00444444'
};

export const bigRegionMeeting: Meeting = {
  busLines: undefined,
  comments: undefined,
  contactEmail1: undefined,
  contactEmail2: undefined,
  contactName1: undefined,
  contactName2: undefined,
  contactPhone1: undefined,
  contactPhone2: undefined,
  day: 5,
  duration: '02:00',
  email: '',
  formatIds: [closedFormat.id, discussionFormat.id],
  id: 1021,
  latitude: 40.712776,
  locationCitySubsection: undefined,
  locationInfo: undefined,
  locationMunicipality: 'Big City',
  locationNation: 'USA',
  locationNeighborhood: undefined,
  locationPostalCode1: '10001',
  locationProvince: 'NY',
  locationStreet: '500 Big Ave',
  locationSubProvince: 'Manhattan',
  locationText: 'Big Region Headquarters',
  longitude: -74.005974,
  name: 'Big Region Gathering',
  phoneMeetingNumber: undefined,
  published: true,
  serviceBodyId: bigRegion.id,
  startTime: '20:00',
  temporarilyVirtual: false,
  timeZone: 'America/New_York',
  trainLines: undefined,
  venueType: 1,
  virtualMeetingAdditionalInfo: undefined,
  virtualMeetingLink: undefined,
  worldId: 'G00555555'
};

export const allServiceBodies: ServiceBody[] = [northernZone, bigRegion, smallRegion, riverCityArea, mountainArea, ruralArea];

export const allFormats: Format[] = [closedFormat, openFormat, discussionFormat, basicTextFormat, jtFormat];

export const allMeetings: Meeting[] = [ruralMeeting, mountainMeeting, riverCityMeeting, bigRegionMeeting, smallRegionMeeting];

const allUsersAndPasswords = [
  { user: serverAdmin, password: 'serveradmin-password' },
  { user: northernZoneAdmin, password: 'northern-zone-password' },
  { user: bigRegionAdmin, password: 'big-region-password' },
  { user: smallRegionAdmin, password: 'small-region-password' },
  { user: riverCityAreaAdmin, password: 'river-city-area-password' },
  { user: mountainAreaAdmin, password: 'mountain-area-password' },
  { user: ruralAreaAdmin, password: 'rural-area-password' },
  { user: smallRegionObserver, password: 'small-region-observer-password' },
  { user: smallRegionDeactivated, password: 'small-region-deactivated-password' },
  { user: bigRegionAdmin2, password: 'big-region2-password' },
  { user: ruralAreaAdmin2, password: 'rural-area2-password' }
];

export const allUsers = allUsersAndPasswords.map((x) => x.user);

// mocked values for editing, creating, and deleting a user
export let mockSavedUserCreate: UserCreate | null;
export let mockSavedUserUpdate: UserUpdate | null;
export let mockSavedUserPartialUpdate: UserPartialUpdate | null;
export let mockDeletedUserId: number | null = null;
let mockSavedUserUpdateId: number | null;
let mockSavedUserPartialUpdateId: number | null;

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
  const mockUser = allUsers.find((u) => u.id === params.userId);
  if (!mockUser || params.userId === mockDeletedUserId) {
    // this error would be thrown if the userId is either something completely random,
    // or of a newly created user (we're not trying to mock that), or a user that was just deleted
    throw new Error('error in mockGetUser -- unknown or recently deleted user');
  }
  // unfortunately svelte might munge the user object, so return a copy
  if (mockSavedUserUpdate && mockUser.id === mockSavedUserUpdateId) {
    return { ...mockUser, ...mockSavedUserUpdate };
  } else if (mockSavedUserPartialUpdate && mockUser.id === mockSavedUserPartialUpdateId) {
    return { ...mockUser, ...mockSavedUserPartialUpdate };
  } else {
    return { ...mockUser };
  }
}

// The value of initOverrides is not used in the mock, so tell the linter it's ok.
// eslint-disable-next-line
async function mockGetUsers(initOverrides?: RequestInit | runtime.InitOverrideFunction): Promise<User[]> {
  if (mockSavedUserCreate || mockSavedUserUpdate || mockSavedUserPartialUpdate || mockDeletedUserId) {
    // If we want mockGetUsers to work after creating, deleting, or updating a user, we'd need to account for
    // the new/changed/deleted user when computing the result.  Right now none of the unit tests need this.
    throw new Error('internal error -- mockGetUsers not set up to work after creating, deleting, or updating a user');
  }
  const userId = get(authenticatedUser)?.id;
  if (!userId) {
    throw new Error('internal error -- trying to get users when no simulated user is logged in');
  }
  // unfortunately svelte might munge the user object, so return a deep copy of allUsers
  const allUsersCopy: User[] = allUsers.map((u) => ({ ...u }));
  if (userId === serverAdmin.id) {
    return allUsersCopy;
  } else {
    return allUsersCopy.filter((u) => u.id === userId || u.ownerId === userId);
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

async function mockCreateUser({ userCreate: user }: { userCreate: UserCreate }): Promise<User> {
  if (mockSavedUserCreate) {
    throw new Error('internal error -- mocking not currently set up to create more than one user');
  }
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

async function mockUpdateUser({ userId: id, userUpdate: u }: { userId: number; userUpdate: UserUpdate }): Promise<void> {
  mockSavedUserUpdateId = id;
  mockSavedUserUpdate = u;
}

async function mockPartialUpdateUser({ userId: id, userPartialUpdate: u }: { userId: number; userPartialUpdate: UserPartialUpdate }): Promise<void> {
  mockSavedUserPartialUpdateId = id;
  mockSavedUserPartialUpdate = u;
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
    return [northernZone, bigRegion, smallRegion, riverCityArea, mountainArea, ruralArea];
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

// Format Mock Functions
async function mockGetFormat(params: { formatId: number }): Promise<Format> {
  const mockFormat = allFormats.find((f) => f.id === params.formatId);
  if (mockFormat) {
    return mockFormat;
  }
  throw new Error('unknown format -- something went wrong');
}

async function mockGetFormats(): Promise<Format[]> {
  const userId = get(authenticatedUser)?.id;
  if (!userId) {
    throw new Error('internal error -- trying to get formats when no simulated user is logged in');
  } else {
    return allFormats;
  }
}

// mocks for editing, creating, and deleting a Format
export let mockSavedFormatCreate: FormatCreate | null;
export let mockSavedFormatUpdate: FormatUpdate | null;
export let mockDeletedFormatId: number | null = null;

async function mockCreateFormat({ formatCreate: format }: { formatCreate: FormatCreate }): Promise<Format> {
  mockSavedFormatCreate = format;
  return {
    translations: format.translations || [{ key: 'O', language: 'en', name: 'Open', description: 'This meeting is open to addicts and non-addicts alike. All are welcome.' }],
    type: format.type || '',
    worldId: format.worldId || '',
    id: 19
  };
}

// eslint-disable-next-line
async function mockUpdateFormat({ formatId: _, formatUpdate: format }: { formatId: number; formatUpdate: FormatUpdate }): Promise<void> {
  mockSavedFormatUpdate = format;
}

async function mockDeleteFormat({ formatId: id }: { formatId: number }): Promise<void> {
  // Do we need to check for dependants? If meeting is using format. this should use foreign keys.
  mockDeletedFormatId = id;
}

// Meeting Mock Functions
async function mockGetMeeting(params: { meetingId: number }): Promise<Meeting> {
  const mockMeeting = allMeetings.find((m) => m.id === params.meetingId);
  if (mockMeeting) {
    return mockMeeting;
  }
  throw new Error('unknown meeting -- something went wrong');
}

async function mockGetMeetings(): Promise<Meeting[]> {
  const userId = get(authenticatedUser)?.id;
  if (!userId) {
    throw new Error('internal error -- trying to get meetings when no simulated user is logged in');
  } else {
    return allMeetings;
  }
}

// mocks for editing, creating, and deleting a Meeting
export let mockSavedMeetingCreate: MeetingCreate | null;
export let mockSavedMeetingUpdate: MeetingUpdate | null;
export let mockDeletedMeetingId: number | null = null;

async function mockCreateMeeting({ meetingCreate: meeting }: { meetingCreate: MeetingCreate }): Promise<Meeting> {
  mockSavedMeetingCreate = meeting;
  return {
    busLines: meeting.busLines || '',
    comments: meeting.comments || '',
    contactEmail1: meeting.contactEmail1 || '',
    contactEmail2: meeting.contactEmail2 || '',
    contactName1: meeting.contactName1 || '',
    contactName2: meeting.contactName2 || '',
    contactPhone1: meeting.contactPhone1 || '',
    contactPhone2: meeting.contactPhone2 || '',
    day: meeting.day || 1,
    duration: meeting.duration || '01:00',
    email: meeting.email || '',
    formatIds: meeting.formatIds || [],
    id: 463,
    latitude: meeting.latitude || 39.4817,
    locationCitySubsection: meeting.locationCitySubsection || '',
    locationInfo: meeting.locationInfo || '',
    locationMunicipality: meeting.locationMunicipality || '',
    locationNation: meeting.locationNation || '',
    locationNeighborhood: meeting.locationNeighborhood || '',
    locationPostalCode1: meeting.locationPostalCode1 || '',
    locationProvince: meeting.locationProvince || '',
    locationStreet: meeting.locationStreet || '',
    locationSubProvince: meeting.locationSubProvince || '',
    locationText: meeting.locationText || '',
    longitude: meeting.longitude || -106.0384,
    name: meeting.name || 'NA Meeting',
    phoneMeetingNumber: meeting.email || '',
    published: meeting.published || false,
    serviceBodyId: mountainArea.id,
    startTime: '19:00',
    temporarilyVirtual: meeting.temporarilyVirtual || false,
    timeZone: meeting.timeZone || 'America/Denver',
    trainLines: meeting.trainLines || '',
    venueType: meeting.venueType || 1,
    virtualMeetingAdditionalInfo: meeting.virtualMeetingAdditionalInfo || '',
    virtualMeetingLink: meeting.virtualMeetingLink || '',
    worldId: meeting.worldId || ''
  };
}

// eslint-disable-next-line
async function mockUpdateMeeting({ meetingId: _, meetingUpdate: meeting }: { meetingId: number; meetingUpdate: MeetingUpdate }): Promise<void> {
  mockSavedMeetingUpdate = meeting;
}

async function mockDeleteMeeting({ meetingId: id }: { meetingId: number }): Promise<void> {
  mockDeletedMeetingId = id;
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
  vi.spyOn(ApiClientWrapper.api, 'partialUpdateUser').mockImplementation(mockPartialUpdateUser);
  vi.spyOn(ApiClientWrapper.api, 'deleteUser').mockImplementation(mockDeleteUser);
  vi.spyOn(ApiClientWrapper.api, 'getServiceBody').mockImplementation(mockGetServiceBody);
  vi.spyOn(ApiClientWrapper.api, 'getServiceBodies').mockImplementation(mockGetServiceBodies);
  vi.spyOn(ApiClientWrapper.api, 'createServiceBody').mockImplementation(mockCreateServiceBody);
  vi.spyOn(ApiClientWrapper.api, 'updateServiceBody').mockImplementation(mockUpdateServiceBody);
  vi.spyOn(ApiClientWrapper.api, 'deleteServiceBody').mockImplementation(mockDeleteServiceBody);
  vi.spyOn(ApiClientWrapper.api, 'getFormat').mockImplementation(mockGetFormat);
  vi.spyOn(ApiClientWrapper.api, 'getFormats').mockImplementation(mockGetFormats);
  vi.spyOn(ApiClientWrapper.api, 'createFormat').mockImplementation(mockCreateFormat);
  vi.spyOn(ApiClientWrapper.api, 'updateFormat').mockImplementation(mockUpdateFormat);
  vi.spyOn(ApiClientWrapper.api, 'deleteFormat').mockImplementation(mockDeleteFormat);
  vi.spyOn(ApiClientWrapper.api, 'getMeeting').mockImplementation(mockGetMeeting);
  vi.spyOn(ApiClientWrapper.api, 'getMeetings').mockImplementation(mockGetMeetings);
  vi.spyOn(ApiClientWrapper.api, 'createMeeting').mockImplementation(mockCreateMeeting);
  vi.spyOn(ApiClientWrapper.api, 'updateMeeting').mockImplementation(mockUpdateMeeting);
  vi.spyOn(ApiClientWrapper.api, 'deleteMeeting').mockImplementation(mockDeleteMeeting);
}

export function sharedBeforeEach() {
  mockSavedUserCreate = null;
  mockSavedUserUpdate = null;
  mockSavedUserUpdateId = null;
  mockSavedUserPartialUpdate = null;
  mockSavedUserPartialUpdateId = null;
  mockDeletedUserId = null;

  mockSavedServiceBodyCreate = null;
  mockSavedServiceBodyUpdate = null;
  mockDeletedServiceBodyId = null;

  mockSavedFormatCreate = null;
  mockSavedFormatUpdate = null;
  mockDeletedFormatId = null;

  mockSavedMeetingCreate = null;
  mockSavedMeetingUpdate = null;
  mockDeletedMeetingId = null;
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
