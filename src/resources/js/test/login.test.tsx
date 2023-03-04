import { fireEvent, render, screen } from '@testing-library/react';
import { describe, it } from 'vitest';

import { strings } from '../localization';
import { Login } from '../pages/Login';
import { provideTheme } from './utils/provideTheme';

describe.only('App', () => {
  beforeAll(() => {
    render(provideTheme(<Login />));
  });
  it('show error if email is invalid'),
    async () => {
      fireEvent.input(screen.getByRole('textbox', { name: /email/i }), {
        target: {
          value: 'test',
        },
      });
    };
});

describe('Login', () => {
  it('renders form title', () => {
    expect(
      screen.getByRole('heading', {
        level: 3,
      }),
    ).toHaveTextContent(strings.loginTitle!);
  });
});
