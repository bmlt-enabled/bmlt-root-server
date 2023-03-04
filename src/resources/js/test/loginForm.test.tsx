import { render, screen, waitFor } from '@testing-library/react';
import { describe, it } from 'vitest';

import { strings } from '../localization';
import LoginForm from '../partials/forms/LoginForm';
import { provideTheme } from './utils/provideTheme';

describe('Login', () => {
  beforeAll(() => {
    render(
      provideTheme(
        <LoginForm
          handleOnSubmit={function (): void {
            throw new Error('Function not implemented.');
          }}
        />,
      ),
    );
  });
  it('renders form title', () => {
    expect(
      screen.getByRole('heading', {
        level: 3,
      }),
    ).toHaveTextContent(strings.loginTitle!);
  });
  // test username field
  it('renders username field', () => {
    waitFor(() => expect(screen.getByTestId('login-username')).toBeInTheDocument());
  });
  it('renders username field with required attribute', () => {
    waitFor(() => expect(screen.getByTestId('login-username')).toBeRequired());
  });
  it('renders username field with type text', () => {
    waitFor(() => expect(screen.getByTestId('login-username')).toHaveAttribute('type', 'text'));
  });
});
