import { render } from '@testing-library/svelte';
import '@testing-library/jest-dom';
import App from '../App.svelte';

test('renders the app component and sets the title', () => {
  render(App);
  expect(document.title).toBe('BMLT Root Server');
});

describe('App.svelte', () => {
  test('renders the app component and sets the title', () => {
    render(App);
    expect(document.title).toBe('BMLT Root Server');
  });
});
