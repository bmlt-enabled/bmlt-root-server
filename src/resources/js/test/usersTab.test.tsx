/* Unit tests for the 'Users' tab

These tests use the following mock users:
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

The tests all start with the same sequence of actions, which are factored out into a helper function:
bypass the login button, and just set the appropriate saved access token for the mocked logged in user
(there are unit tests for the login button in loginForm.test.tsx), and then click the 'Users' tab.

Test that the 'User' dropdown menu has the correct choices when logged in as some representative users:
  - For the Server Administrator, these should be 'Create New User', plus 7 mock users (all mock users
    except for the Server Administrator itself), since the server administrator can access them all.
    The default should be 'Create New User' even though this isn't first alphabetically.
  - For Northern Zone, we should see the 2 regions under it plus the observer.  'Create New User'
    shouldn't appear, since only the server administrator can do that.  The default should be Big Region,
    since that comes first alphabetically.
  - For Big Region, we should see the 3 areas under it.  The default should be Mountain Area.
  - For Small Region, we should see '- None -', since this user has no children.
  - For Small Region Observer, we should see '- None', since it's an observer.

Test that the contents of all the fields displayed in the editor for these representative cases:
  - logged in as serveradmin, Create New User
  - logged in as serveradmin, edit Big Region
  - logged in as Big Region, edit Rural Area
Also test the contents of the dropdown menus for 'User Is A:' and 'Owned By:' for the serveradmin cases.  Test
that they are disabled when logged in as Big Region.

Test editing each of the fields and saving, for the Server Administrator and Create New User,
and for Big Region editing Rural Area.

Later: the UI should indicate when the form is dirty, and also provide a 'cancel' button.  When this is
added, appropriate unit tests should be added as well.
*/
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { Token, User } from 'bmlt-root-server-client';
import { afterAll, beforeAll, beforeEach, describe, expect, test, vi } from 'vitest';

import App from '../App';
import ApiClientWrapper from '../RootServerApi';

const mockServerAdmin: User = {
  description: 'Main Server Administrator',
  displayName: 'Server Administrator',
  email: 'mockadmin@bmlt.app',
  id: 1,
  ownerId: '',
  type: 'admin',
  username: 'serveradmin',
};

const mockNorthernZoneAdmin: User = {
  description: 'Northern Zone Administrator',
  displayName: 'Northern Zone',
  email: 'nzone@bmlt.app',
  id: 2,
  ownerId: '',
  type: 'serviceBodyAdmin',
  username: 'NorthernZone',
};

const mockBigRegionAdmin: User = {
  description: 'Big Region Administrator',
  displayName: 'Big Region',
  email: 'big@bmlt.app',
  id: 3,
  ownerId: '2',
  type: 'serviceBodyAdmin',
  username: 'BigRegion',
};

const mockSmallRegionAdmin: User = {
  description: 'Small Region Administrator',
  displayName: 'Small Region',
  email: 'small@bmlt.app',
  id: 4,
  ownerId: '2',
  type: 'serviceBodyAdmin',
  username: 'SmallRegion',
};

const mockRiverCityAreaAdmin: User = {
  description: 'River City Area Administrator',
  displayName: 'River City Area',
  email: 'river@bmlt.app',
  id: 5,
  ownerId: '3',
  type: 'serviceBodyAdmin',
  username: 'RiverCityArea',
};

const mockMountainAreaAdmin: User = {
  description: 'Mountain Area Administrator',
  displayName: 'Mountain Area',
  email: 'mountain@bmlt.app',
  id: 6,
  ownerId: '3',
  type: 'serviceBodyAdmin',
  username: 'MountainArea',
};

const mockRuralAreaAdmin: User = {
  description: 'Rural Area Administrator',
  displayName: 'Rural Area',
  email: 'rural@bmlt.app',
  id: 7,
  ownerId: '3',
  type: 'serviceBodyAdmin',
  username: 'RuralArea',
};

const mockSmallRegionObserver: User = {
  description: 'Small Region Observer',
  displayName: 'Small Observer',
  email: 'smallobserver@bmlt.app',
  id: 8,
  ownerId: '2',
  type: 'observer',
  username: 'SmallObserver',
};

const allMockUsers = [
  mockServerAdmin,
  mockNorthernZoneAdmin,
  mockBigRegionAdmin,
  mockSmallRegionAdmin,
  mockRiverCityAreaAdmin,
  mockMountainAreaAdmin,
  mockRuralAreaAdmin,
  mockSmallRegionObserver,
];

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

// Declaration with a type would be:
//    async function mockGetUsers(initOverrides?: RequestInit | runtime.InitOverrideFunction): Promise<User>[] {
// But this gives an error -- so using any instead for the type.
// Also the value of initOverrides is not used, so using _ for the name.
async function mockGetUsers(_?: any): Promise<User[]> {
  if (!savedAccessToken) {
    throw new Error('internal error -- trying to get users when no simulated user is logged in');
  } else if (savedAccessToken.userId === mockServerAdmin.id) {
    return [
      mockServerAdmin,
      mockNorthernZoneAdmin,
      mockBigRegionAdmin,
      mockSmallRegionAdmin,
      mockRiverCityAreaAdmin,
      mockMountainAreaAdmin,
      mockRuralAreaAdmin,
      mockSmallRegionObserver,
    ];
  } else if (savedAccessToken.userId === mockNorthernZoneAdmin.id) {
    return [mockNorthernZoneAdmin, mockBigRegionAdmin, mockSmallRegionAdmin, mockSmallRegionObserver];
  } else if (savedAccessToken.userId === mockBigRegionAdmin.id) {
    return [mockBigRegionAdmin, mockRiverCityAreaAdmin, mockMountainAreaAdmin, mockRuralAreaAdmin];
  } else if (savedAccessToken.userId === mockSmallRegionAdmin.id) {
    return [mockSmallRegionAdmin];
  } else if (savedAccessToken.userId === mockRiverCityAreaAdmin.id) {
    return [mockRiverCityAreaAdmin];
  } else if (savedAccessToken.userId === mockMountainAreaAdmin.id) {
    return [mockMountainAreaAdmin];
  } else if (savedAccessToken.userId === mockRuralAreaAdmin.id) {
    return [mockRuralAreaAdmin];
  } else if (savedAccessToken.userId === mockSmallRegionObserver.id) {
    return [mockSmallRegionObserver];
  } else {
    throw new Error('internal error -- user ID not found in mockGetUsers');
  }
}

function mockIsLoggedIn(): boolean {
  return Boolean(savedAccessToken);
}

async function mockAuthToken(_: { tokenCredentials: { username: string; password: string } }): Promise<Token> {
  throw new Error(
    'should not be called in this test suite -- logging in should be mocked in these tests without using the login menu item',
  );
}

async function mockAuthLogout(): Promise<void> {
  throw new Error('should not be called in this test suite');
}

// Helper function to simulate logging in and then selecting the Users tab
// (this sequence is used by all the tests in this file).
// Not sure how to declare return type  -- UserEvent not defined.  Thought it should be:
// async function startWithUsersTab(loggedInUser: User): Promise<UserEvent> {
async function startWithUsersTab(loggedInUser: User) {
  const user = userEvent.setup();
  // bypass login procedure and just assert loggedInUser is logged in
  savedAccessToken = generateMockToken(loggedInUser);
  render(<App />);
  const link = await screen.findByRole('link', { name: 'Users' });
  await user.click(link);
  return user;
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

describe('check for correct lists of users in User dropdown menu', () => {
  test('logged in as serveradmin', async () => {
    const user = await startWithUsersTab(mockServerAdmin);
    // The default should be 'Create New User'.  This default should be what is shown before
    // clicking on the dropdown
    await screen.findByRole('button', { name: /create new user/i });
    // Now click the dropdown menu.  We should see all of the users (except the serveradmin),
    // plus an option to create a new user.
    const dropdown = await screen.findByLabelText('User');
    await user.click(dropdown);
    const allOptions = await screen.findAllByRole('option');
    expect(allOptions.length).toBe(8);
    await screen.findByRole('option', { name: 'Northern Zone' });
    await screen.findByRole('option', { name: 'Big Region' });
    await screen.findByRole('option', { name: 'Small Region' });
    await screen.findByRole('option', { name: 'River City Area' });
    await screen.findByRole('option', { name: 'Mountain Area' });
    await screen.findByRole('option', { name: 'Rural Area' });
    await screen.findByRole('option', { name: 'Small Observer' });
    await screen.findByRole('option', { name: 'Create New User' });
  });

  test('logged in as Northern Zone', async () => {
    const user = await startWithUsersTab(mockNorthernZoneAdmin);
    // The User dropdown should start with Big Region selected since it comes first alphabetically
    await screen.findByText(/Big Region/);
    await screen.findByText(/User ID #3/);
    // Now click the dropdown menu.  We should see the two regions in the list, and none of the other users.
    const dropdown = await screen.findByLabelText('User');
    await user.click(dropdown);
    await screen.findByRole('option', { name: 'Big Region' });
    await screen.findByRole('option', { name: 'Small Region' });
    await screen.findByRole('option', { name: 'Small Observer' });
    const allOptions = await screen.findAllByRole('option');
    expect(allOptions.length).toBe(3);
  });

  test('logged in as Big Region', async () => {
    const user = await startWithUsersTab(mockBigRegionAdmin);
    await screen.findByText(/Mountain Area/);
    await screen.findByText(/User ID #6/);
    // Now click the dropdown menu.  We should see the 3 areas in the list, and none of the other users.
    const dropdown = await screen.findByLabelText('User');
    await user.click(dropdown);
    await screen.findByRole('option', { name: 'Mountain Area' });
    await screen.findByRole('option', { name: 'River City Area' });
    await screen.findByRole('option', { name: 'Rural Area' });
    const allOptions = await screen.findAllByRole('option');
    expect(allOptions.length).toBe(3);
  });

  test('logged in as Small Region', async () => {
    await startWithUsersTab(mockSmallRegionAdmin);
    await screen.findByText(/- None -/);
  });
});

describe('check the contents of all the fields displayed in the editor', () => {
  test('logged in as serveradmin; select Create New User', async () => {
    const user = await startWithUsersTab(mockServerAdmin);
    const selectUser = await screen.findByLabelText('User');
    await user.click(selectUser);
    const newUser = await screen.findByRole('option', { name: 'Create New User' });
    await user.click(newUser);
    // the 'User Is A:' and 'Owned By:' buttons should be enabled, but no need to test for that here --
    // that gets tested later when we click them
    const userType = await screen.findByRole('button', { name: 'User Is A:' });
    expect(userType.textContent).toBe('Service Body Administrator');
    const ownedBy = await screen.findByRole('button', { name: 'Owned By:' });
    expect(ownedBy.textContent).toBe('Server Administrator');
    const userName = (await screen.findByRole('textbox', { name: 'Username' })) as HTMLInputElement;
    expect(userName.value).toBe('');
    const displayName = (await screen.findByRole('textbox', { name: 'Name' })) as HTMLInputElement;
    expect(displayName.value).toBe('');
    const email = (await screen.findByRole('textbox', { name: 'Email' })) as HTMLInputElement;
    expect(email.value).toBe('');
    // TODO: this doesn't work if it's ('Password') -- only works if it's a regular expression. ???
    const password = (await screen.findByLabelText(/Password/)) as HTMLInputElement;
    expect(password.value).toBe('');
    const description = (await screen.findByRole('textbox', { name: 'Description' })) as HTMLInputElement;
    expect(description.value).toBe('');
  });

  test('logged in as serveradmin; editing Big Region', async () => {
    const user = await startWithUsersTab(mockServerAdmin);
    // click the dropdown menu, and then select Big Region
    const selectUser = await screen.findByLabelText('User');
    await user.click(selectUser);
    const big = await screen.findByRole('option', { name: 'Big Region' });
    await user.click(big);
    await screen.findByText(/User ID #3/);
    const userType = await screen.findByRole('button', { name: 'User Is A:' });
    expect(userType.textContent).toBe('Service Body Administrator');
    const ownedBy = await screen.findByRole('button', { name: 'Owned By:' });
    expect(ownedBy.textContent).toBe('Northern Zone');
    const userName = (await screen.findByRole('textbox', { name: 'Username' })) as HTMLInputElement;
    expect(userName.value).toBe('BigRegion');
    const displayName = (await screen.findByRole('textbox', { name: 'Name' })) as HTMLInputElement;
    expect(displayName.value).toBe('Big Region');
    const email = (await screen.findByRole('textbox', { name: 'Email' })) as HTMLInputElement;
    expect(email.value).toBe('big@bmlt.app');
    const password = (await screen.findByLabelText('Password')) as HTMLInputElement;
    expect(password.value).toBe('');
    const description = (await screen.findByRole('textbox', { name: 'Description' })) as HTMLInputElement;
    expect(description.value).toBe('Big Region Administrator');
  });

  test('logged in as Big Region; editing Rural Area', async () => {
    const user = await startWithUsersTab(mockBigRegionAdmin);
    const selectUser = await screen.findByLabelText('User');
    await user.click(selectUser);
    const rural = await screen.findByRole('option', { name: 'Rural Area' });
    await user.click(rural);
    await screen.findByText(/User ID #7/);
    // the 'User Is A:' and 'Owned By:' buttons should be disabled
    const userType = await screen.findByRole('button', { name: 'User Is A:' });
    expect(userType.textContent).toBe('Service Body Administrator');
    expect(userType.attributes.getNamedItem('aria-disabled')?.value).toBe('true');
    // TODO: this is simpler but doesn't work:
    //       expect(userType).toBeDisabled();
    // In fact, toBeEnabled() succeeds instead.  This might indicate that the way the 'User Is A:'
    // and 'Owned By:' menus are being disabled could be improved.
    const ownedBy = await screen.findByRole('button', { name: 'Owned By:' });
    expect(ownedBy.textContent).toBe('Big Region');
    expect(ownedBy.attributes.getNamedItem('aria-disabled')?.value).toBe('true');
    const userName = (await screen.findByRole('textbox', { name: 'Username' })) as HTMLInputElement;
    expect(userName.value).toBe('RuralArea');
    const displayName = (await screen.findByRole('textbox', { name: 'Name' })) as HTMLInputElement;
    expect(displayName.value).toBe('Rural Area');
    const email = (await screen.findByRole('textbox', { name: 'Email' })) as HTMLInputElement;
    expect(email.value).toBe('rural@bmlt.app');
    const password = (await screen.findByLabelText('Password')) as HTMLInputElement;
    expect(password.value).toBe('');
    const description = (await screen.findByRole('textbox', { name: 'Description' })) as HTMLInputElement;
    expect(description.value).toBe('Rural Area Administrator');
  });
});

describe('Check the contents of the dropdown menu for User Is A:', () => {
  test('logged in as serveradmin; use default selection of Create New User', async () => {
    const user = await startWithUsersTab(mockServerAdmin);
    // Create New User should be selected by default, so we don't need to select it explicitly
    // (there are other tests that select something explicitly from this menu)
    const selectUserType = await screen.findByRole('button', { name: 'User Is A:' });
    await user.click(selectUserType);
    const allOptions = await screen.findAllByRole('option');
    expect(allOptions.length).toBe(3);
    await screen.findByRole('option', { name: 'Service Body Administrator' });
    await screen.findByRole('option', { name: 'Service Body Observer' });
    await screen.findByRole('option', { name: 'Deactivated User' });
  });

  test('logged in as serveradmin; editing Big Region', async () => {
    const user = await startWithUsersTab(mockServerAdmin);
    const selectUser = await screen.findByLabelText('User');
    await user.click(selectUser);
    const big = await screen.findByRole('option', { name: 'Big Region' });
    await user.click(big);
    const selectUserType = await screen.findByRole('button', { name: 'User Is A:' });
    await user.click(selectUserType);
    const allOptions = await screen.findAllByRole('option');
    expect(allOptions.length).toBe(3);
    await screen.findByRole('option', { name: 'Service Body Administrator' });
    await screen.findByRole('option', { name: 'Service Body Observer' });
    await screen.findByRole('option', { name: 'Deactivated User' });
  });

  // no test for the case 'logged in as Big Region; editing Rural Area' since the 'User Is A:' menu is disabled
  // (there is an earlier test that it is disabled)
});

describe('Check the contents of the dropdown menu for Owned By:', () => {
  test('logged in as serveradmin; use default selection of Create New User', async () => {
    const user = await startWithUsersTab(mockServerAdmin);
    const selectOwner = await screen.findByRole('button', { name: 'Owned By:' });
    await user.click(selectOwner);
    // It doesn't make sense for a user to be its own owner, and probably these should also be
    // restricted to respect the hierarchy.  But this unit test reflects the current new UI
    // implementation, which in turn follows the old UI implementation; and those choices are allowed.
    const allOptions = await screen.findAllByRole('option');
    expect(allOptions.length).toBe(8);
    await screen.findByRole('option', { name: 'Server Administrator' });
    await screen.findByRole('option', { name: 'Northern Zone' });
    await screen.findByRole('option', { name: 'Big Region' });
    await screen.findByRole('option', { name: 'Small Region' });
    await screen.findByRole('option', { name: 'River City Area' });
    await screen.findByRole('option', { name: 'Mountain Area' });
    await screen.findByRole('option', { name: 'Rural Area' });
    await screen.findByRole('option', { name: 'Small Observer' });
  });

  test('logged in as serveradmin; select Create New User', async () => {
    const user = await startWithUsersTab(mockServerAdmin);
    const selectUser = await screen.findByLabelText('User');
    await user.click(selectUser);
    const newUser = await screen.findByRole('option', { name: 'Create New User' });
    await user.click(newUser);
    const selectOwner = await screen.findByRole('button', { name: 'Owned By:' });
    await user.click(selectOwner);
    const allOptions = await screen.findAllByRole('option');
    expect(allOptions.length).toBe(8);
    await screen.findByRole('option', { name: 'Server Administrator' });
    await screen.findByRole('option', { name: 'Northern Zone' });
    await screen.findByRole('option', { name: 'Big Region' });
    await screen.findByRole('option', { name: 'Small Region' });
    await screen.findByRole('option', { name: 'River City Area' });
    await screen.findByRole('option', { name: 'Mountain Area' });
    await screen.findByRole('option', { name: 'Rural Area' });
    await screen.findByRole('option', { name: 'Small Observer' });
  });

  test('logged in as serveradmin; editing Big Region', async () => {
    const user = await startWithUsersTab(mockServerAdmin);
    const selectUser = await screen.findByLabelText('User');
    await user.click(selectUser);
    const big = await screen.findByRole('option', { name: 'Big Region' });
    await user.click(big);
    const selectOwner = await screen.findByRole('button', { name: 'Owned By:' });
    await user.click(selectOwner);
    // It doesn't make sense for a user to be its own owner, and probably these should also be
    // restricted to respect the hierarchy.  But this unit test reflects the current new UI
    // implementation, which in turn follows the old UI implementation; and those choices are allowed.
    const allOptions = await screen.findAllByRole('option');
    expect(allOptions.length).toBe(8);
    await screen.findByRole('option', { name: 'Server Administrator' });
    await screen.findByRole('option', { name: 'Northern Zone' });
    await screen.findByRole('option', { name: 'Big Region' });
    await screen.findByRole('option', { name: 'Small Region' });
    await screen.findByRole('option', { name: 'River City Area' });
    await screen.findByRole('option', { name: 'Mountain Area' });
    await screen.findByRole('option', { name: 'Rural Area' });
    await screen.findByRole('option', { name: 'Small Observer' });
  });

  // no test for the case 'logged in as Big Region; editing Rural Area' since the 'Owned By:' menu is disabled
  // (there is an earlier test that it is disabled)
});

afterAll(async () => {
  vi.restoreAllMocks();
});
