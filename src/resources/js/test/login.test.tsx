import { cleanup, fireEvent, render, screen } from '@testing-library/react';
import { BrowserRouter } from 'react-router-dom';
import { describe, it } from 'vitest';

import { Login } from '../pages/Login';
import { provideTheme } from './utils/provideTheme';

describe('Login', () => {
  beforeEach(() => {
    render(
      provideTheme(
        <BrowserRouter>
          <Login />
        </BrowserRouter>,
      ),
    );
  });
  afterEach(cleanup);
  it('onSubmit check validation for empty username and password fields', async () => {
    fireEvent.submit(
      screen.getByRole('button', {
        name: /log in/i,
      }),
    );
    expect(await screen.findByText(/username is required/i)).toBeInTheDocument();
    expect(await screen.findByText(/password is required/i)).toBeInTheDocument();
  });

  // need to test react-hook-form and test for validation
});
