import { writable } from 'svelte/store';
import type { ServiceBody } from 'bmlt-root-server-client';
import RootServerApi from '../lib/RootServerApi';

export const serviceBodiesData = writable<ServiceBody[]>([]);
export const isLoaded = writable(false);

export async function fetchServiceBodies() {
  try {
    isLoaded.set(false);
    const data = await RootServerApi.getServiceBodies();
    serviceBodiesData.set(data);
    isLoaded.set(true);
  } catch (error: any) {
    await RootServerApi.handleErrors(error);
  }
}
