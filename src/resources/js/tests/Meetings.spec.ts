import { beforeAll, beforeEach, describe, test } from 'vitest';
import { screen } from '@testing-library/svelte';
import { login, sharedAfterEach, sharedBeforeAll, sharedBeforeEach } from './sharedDataAndMocks';

beforeAll(sharedBeforeAll);
beforeEach(sharedBeforeEach);
afterEach(sharedAfterEach);

describe('check content in Meetings tab when logged in as various users', () => {
  test('check layout when logged in as serveradmin', async () => {
    const user = await login('serveradmin', 'Meetings');
    const serviceBodiesButton = await screen.findByRole('button', { name: 'Service Bodies' });
    expect(serviceBodiesButton).toBeInTheDocument();
    const dayButton = await screen.findByRole('button', { name: 'Day' });
    expect(dayButton).toBeInTheDocument();
    const addMeetingButton = await screen.findByRole('button', { name: 'Add Meeting' });
    expect(addMeetingButton).toBeInTheDocument();
    await user.click(await screen.findByRole('button', { name: 'Search' }));
    const cells = await screen.findAllByRole('cell');
    expect(cells.length).toBe(20);
    expect(screen.getByRole('cell', { name: 'Real Talk' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'Mountain Meeting' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'River Reflections' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'Small Beginnings' })).toBeInTheDocument();
    expect(screen.getByRole('cell', { name: 'Big Region Gathering' })).toBeInTheDocument();
    await user.click(await screen.findByRole('cell', { name: 'Big Region Gathering' }));
    const day = screen.getByRole('combobox', { name: 'Weekday' }) as HTMLSelectElement;
    expect(day.value).toBe('5');
  });
});
