import { writable } from 'svelte/store';

const spinnerStore = writable(0);
export const spinner = {
  subscribe: spinnerStore.subscribe,
  show: (): void => spinnerStore.update((n) => n + 1),
  hide: (): void => spinnerStore.update((n) => n - 1)
};
