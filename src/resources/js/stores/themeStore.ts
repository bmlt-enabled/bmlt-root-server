import { writable } from 'svelte/store';

const storedTheme = typeof window !== 'undefined' ? window.localStorage.getItem('theme') : null;
export const theme = writable(storedTheme || 'dark'); // Default to dark if no theme is stored

theme.subscribe((value) => {
  if (typeof window !== 'undefined') {
    window.localStorage.setItem('theme', value);
    document.body.style.backgroundColor = value === 'light' ? '#fff' : '#333';
  }
});
