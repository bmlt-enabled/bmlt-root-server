import { writable } from 'svelte/store';
import type { User } from 'bmlt-root-server-client';
import RootServerApi from '../lib/RootServerApi';

export const usersData = writable<User[]>([]);
export const isLoaded = writable(false);

export async function fetchUsers() {
  try {
    isLoaded.set(false);
    const data = await RootServerApi.getUsers();
    usersData.set(data);
    isLoaded.set(true);
  } catch (error: any) {
    await RootServerApi.handleErrors(error);
  }
}
