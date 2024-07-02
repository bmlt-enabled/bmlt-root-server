import { writable } from 'svelte/store';
import RootServerApi from '../lib/RootServerApi';

export const isLoggedIn = writable(false);
export const isTokenExpired = writable(false);

export function checkAuth() {
  const token = localStorage.getItem('token');
  if (token) {
    isTokenExpired.set(RootServerApi.isTokenExpired);
    isLoggedIn.set(RootServerApi.isLoggedIn);
  } else {
    isLoggedIn.set(false);
    isTokenExpired.set(true);
  }
}
