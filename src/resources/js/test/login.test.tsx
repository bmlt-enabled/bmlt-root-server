import { fireEvent, render, screen } from '@testing-library/react';
import { BrowserRouter } from 'react-router-dom';
import { describe, it, vi } from 'vitest';

import { Login } from '../pages/Login';
import { provideTheme } from './utils/provideTheme';

// mock login function from RootServerApi
vi.mock('../RootServerApi', () => {
  const login = vi.fn();
  login.mockResolvedValue({
    token: 'token',
  });
  return {
    login,
  };
});

// mock handleErrors function from RootServerApi
vi.mock('../RootServerApi', () => {
  const handleErrors = vi.fn();
  handleErrors.mockResolvedValue({
    handleAuthenticationError: (error: any) => {
      error.message = 'Invalid username or password';
    },
    handleValidationError: (error: any) => {
      error.errors = {
        username: ['Username is required'],
        password: ['Password is required'],
      };
    },
  });
  return {
    handleErrors,
  };
});

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
  afterEach(() => {
    vi.restoreAllMocks();
  });

  it('onSubmit check validation for empty username and password fields', async () => {
    fireEvent.submit(
      screen.getByRole('button', {
        name: /log in/i,
      }),
    );
    expect(await screen.findByText(/username is required/i)).toBeInTheDocument();
    expect(await screen.findByText(/password is required/i)).toBeInTheDocument();
  });

  it('onSubmit check validation for empty username field', async () => {
    fireEvent.input(screen.getByLabelText(/username/i), {
      target: { value: '' },
    });
    fireEvent.submit(
      screen.getByRole('button', {
        name: /log in/i,
      }),
    );
    expect(await screen.findByText(/username is required/i)).toBeInTheDocument();
  });

  it('onSubmit check validation for empty password field', async () => {
    fireEvent.input(screen.getByLabelText(/password/i), {
      target: { value: '' },
    });
    fireEvent.submit(
      screen.getByRole('button', {
        name: /log in/i,
      }),
    );
    expect(await screen.findByText(/password is required/i)).toBeInTheDocument();
  });

  it('onSubmit check for error message when username or password is incorrect', async () => {
    fireEvent.input(screen.getByLabelText(/username/i), {
      target: { value: 'username' },
    });
    fireEvent.input(screen.getByLabelText(/password/i), {
      target: { value: 'password' },
    });
    fireEvent.submit(
      screen.getByRole('button', {
        name: /log in/i,
      }),
    );
    expect(await screen.findByText(/username is required/i)).toBeInTheDocument();
    expect(await screen.findByText(/password is required/i)).toBeInTheDocument();
  });
});
