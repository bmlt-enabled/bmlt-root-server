import { fireEvent, render, screen } from '@testing-library/react';
import user from '@testing-library/user-event';
import { describe, it } from 'vitest';

import { Login } from '../pages/Login';
import { provideTheme } from './utils/provideTheme';

describe('Login', () => {
  const onSubmit = jest.fn();
  beforeEach(() => {
    onSubmit.mockClear();
    render(provideTheme(<Login />));
  });
  it('onSubmit make sure all fields pass validation', () => {
    user.type(getUsername(), 'username');
    user.type(getPassword(), 'password');
  });
});

function getUsername() {
  return screen.getByRole('textbox', {
    name: /username/i,
  });
}

function getPassword() {
  return screen.getByTestId('login-password');
}
