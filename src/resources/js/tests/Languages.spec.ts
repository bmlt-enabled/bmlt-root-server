import { render, screen } from '@testing-library/svelte';
// import userEvent from '@testing-library/user-event';
import '@testing-library/jest-dom';
import { beforeAll, beforeEach, describe, expect, test } from 'vitest';
import App from '../App.svelte';
import { setupMocks, sharedBeforeEach } from './sharedDataAndMocks';

beforeAll(async () => {
  setupMocks();
});

beforeEach(async () => {
  sharedBeforeEach();
});

describe('language selection tests', () => {
  test('check language selection menu', () => {
    render(App);
    const langs: HTMLSelectElement = screen.getByRole('combobox', { name: 'Select Language' });
    expect(langs.length).toBe(4);
    expect(langs.item(0)?.label).toBe('Choose option ...');
    expect(langs.item(1)?.label).toBe('English');
    expect(langs.item(2)?.label).toBe('Deutsch');
    expect(langs.item(3)?.label).toBe('Français');
  });

  test('test isLanguageSelectorEnabled == false', () => {
    const settings = (global as any).settings;
    settings['isLanguageSelectorEnabled'] = false;
    render(App);
    expect(screen.queryByRole('combobox', { name: 'Select Language' })).toBeNull();
  });
  // TODO: test clicking on German and checking the labels -- see last login test in the react code
});
