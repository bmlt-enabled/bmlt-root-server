import { render, screen } from '@testing-library/react';
import { describe, it } from 'vitest';

import { Login } from '../pages/Login';

describe('Login', () => {
  it('renders form title', () => {
    // arrange
    render(<Login />);
    // act
    // expect
    expect(
      screen.getByRole('heading', {
        level: 3,
      }),
    );
  });
});
