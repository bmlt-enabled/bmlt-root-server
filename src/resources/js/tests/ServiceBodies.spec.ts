import { beforeAll, beforeEach, describe, test } from 'vitest';
import { screen } from '@testing-library/svelte';
import '@testing-library/jest-dom';
import { login, sharedAfterEach, sharedBeforeAll, sharedBeforeEach } from './sharedDataAndMocks';

beforeAll(sharedBeforeAll);
beforeEach(sharedBeforeEach);
afterEach(sharedAfterEach);

describe('check content in Service Body tab when logged in as various users', () => {
  test('check layout when logged in as serveradmin', async () => {
    await login('serveradmin', 'Service Bodies');
    expect(await screen.findByRole('heading', { name: 'Service Bodies', level: 2 })).toBeInTheDocument();
    expect(await screen.findByRole('textbox', { name: 'Search' })).toBeInTheDocument();
    // There should be 6 service bodies, with 2 cells per user (name and a delete icon)
    const cells = screen.getAllByRole('cell');
    expect(cells.length).toBe(12);
    // check for a couple of representative service bodies
    expect(screen.getByRole('cell', { name: 'Big Region' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'Rural Area' })).toBeInTheDocument();
  });

  test('check layout when logged in as Northern Zone', async () => {
    await login('NorthernZone', 'Service Bodies');
    // There should be 4 users, with 1 cell per user (display name but no delete icon)
    const cells = await screen.findAllByRole('cell');
    expect(cells.length).toBe(6);
    expect(screen.getByRole('cell', { name: 'Big Region' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'Rural Area' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'Mountain Area' })).toBeInTheDocument();
  });
});

describe('check editing, adding, and deleting service bodies using the popup dialog boxes', () => {
  test('test Confirm modal appears when attempting to close with unsaved changes', async () => {
    const user = await login('serveradmin', 'Service Bodies');
    await user.click(screen.getByRole('link', { name: 'Service Bodies', hidden: true }));
    await user.click(screen.getByRole('cell', { name: 'Rural Area' }));
    const helpline = screen.getByRole('textbox', { name: 'Helpline' }) as HTMLInputElement;
    await user.clear(helpline);
    await user.type(helpline, '555-867-5309');
    await user.click(await screen.findByRole('button', { name: 'Close modal' }));
    expect(screen.getByText('You have unsaved changes. Do you really want to close?')).toBeInTheDocument();
  });
});
