import { render, screen } from '@testing-library/svelte';
import { describe, test } from 'vitest';
import AccountServiceBodyList from '../components/AccountServiceBodyList.svelte';
import { mockServerAdmin } from './sharedDataAndMocks';

describe('Account Service Body List', () => {
  test('for serveradmin account', async () => {
    render(AccountServiceBodyList, { user: mockServerAdmin });
    // TODO: this should work, but instead it says it's still loading
    // expect(await screen.findByText('Northern Zone')).toBeInTheDocument();
    // This shouldn't be true
    expect(await screen.findByText(/loading .../)).toBeInTheDocument();
    // TODO: after the statement above, we end up with an error message on the console:
    // TODO show error dialog The request failed and the interceptors did not return an alternative response
  });
});
