import { render, screen, waitFor } from '@testing-library/svelte';
import { describe, test } from 'vitest';

import AccountServiceBodyList from '../components/AccountServiceBodyList.svelte';
import { authenticatedUser } from '../stores/apiCredentials';
import { bigRegionAdmin, bigRegionAdmin2, northernZoneAdmin, smallRegionAdmin, smallRegionObserver, serverAdmin, sharedAfterEach, sharedBeforeAll, sharedBeforeEach } from './sharedDataAndMocks';

beforeAll(sharedBeforeAll);
beforeEach(sharedBeforeEach);
afterEach(sharedAfterEach);

describe('Account Service Body List', () => {
  test('for serveradmin account', async () => {
    authenticatedUser.set(serverAdmin);
    render(AccountServiceBodyList, { user: serverAdmin });
    await waitFor(() => {
      expect(screen.queryByText('Northern Zone')).toBeInTheDocument();
      expect(screen.queryByText('Big Region')).toBeInTheDocument();
      expect(screen.queryByText('Small Region')).toBeInTheDocument();
      expect(screen.queryByText('River City Area')).toBeInTheDocument();
      expect(screen.queryByText('Mountain Area')).toBeInTheDocument();
      expect(screen.queryByText('Rural Area')).toBeInTheDocument();
    });
  });

  test('for Northern Zone admin', async () => {
    authenticatedUser.set(northernZoneAdmin);
    render(AccountServiceBodyList, { user: northernZoneAdmin });
    await waitFor(() => {
      expect(screen.queryByText('Northern Zone')).toBeInTheDocument();
      expect(screen.queryByText('Big Region')).toBeInTheDocument();
      expect(screen.queryByText('Small Region')).toBeInTheDocument();
      expect(screen.queryByText('River City Area')).toBeInTheDocument();
      expect(screen.queryByText('Mountain Area')).toBeInTheDocument();
      expect(screen.queryByText('Rural Area')).toBeInTheDocument();
    });
  });

  test('for Big Region admin', async () => {
    authenticatedUser.set(bigRegionAdmin);
    render(AccountServiceBodyList, { user: bigRegionAdmin });
    await waitFor(() => {
      expect(screen.queryByText('Northern Zone')).toBe(null);
      expect(screen.queryByText('Big Region')).toBeInTheDocument();
      expect(screen.queryByText('Small Region')).toBe(null);
      expect(screen.queryByText('River City Area')).toBeInTheDocument();
      expect(screen.queryByText('Mountain Area')).toBeInTheDocument();
      expect(screen.queryByText('Rural Area')).toBeInTheDocument();
    });
  });

  test('for Big Region admin 2', async () => {
    authenticatedUser.set(bigRegionAdmin2);
    render(AccountServiceBodyList, { user: bigRegionAdmin2 });
    await waitFor(() => {
      expect(screen.queryByText('Northern Zone')).toBe(null);
      expect(screen.queryByText('Big Region')).toBeInTheDocument();
      expect(screen.queryByText('Small Region')).toBe(null);
      expect(screen.queryByText('River City Area')).toBeInTheDocument();
      expect(screen.queryByText('Mountain Area')).toBeInTheDocument();
      expect(screen.queryByText('Rural Area')).toBeInTheDocument();
    });
  });

  test('for Small Region admin', async () => {
    authenticatedUser.set(smallRegionAdmin);
    render(AccountServiceBodyList, { user: smallRegionAdmin });
    await waitFor(() => {
      expect(screen.queryByText('Northern Zone')).toBe(null);
      expect(screen.queryByText('Big Region')).toBe(null);
      expect(screen.queryByText('Small Region')).toBeInTheDocument();
      expect(screen.queryByText('River City Area')).toBe(null);
      expect(screen.queryByText('Mountain Area')).toBe(null);
      expect(screen.queryByText('Rural Area')).toBe(null);
    });
  });

  test('for Small Region observer', async () => {
    authenticatedUser.set(smallRegionObserver);
    render(AccountServiceBodyList, { user: smallRegionObserver });
    await waitFor(() => {
      expect(screen.queryByText('- None -')).toBeInTheDocument();
    });
  });
});
