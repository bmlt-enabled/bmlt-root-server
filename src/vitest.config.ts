import { defineConfig } from 'vitest/config';
import { svelte } from '@sveltejs/vite-plugin-svelte';
import sveltePreprocess from 'svelte-preprocess';

export default defineConfig({
  plugins: [
    svelte({
      preprocess: sveltePreprocess(),
    }),
  ],
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: './resources/js/tests/setup.ts',
    include: ['resources/js/**/*.{test,spec}.{js,ts}'],
    transformMode: {
      web: [/\.svelte$/],
    },
  },
});
