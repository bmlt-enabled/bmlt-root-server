import { defineConfig } from 'vite';
import { svelte } from '@sveltejs/vite-plugin-svelte';
import laravel from 'laravel-vite-plugin';
import { svelteTesting } from '@testing-library/svelte/vite';

export default defineConfig({
  plugins: [
    svelte(),
    svelteTesting(),
    laravel({
      input: ['resources/js/app.ts'],
      refresh: true
    })
  ],
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: './resources/js/tests/setup.ts',
    include: ['resources/js/**/*.{test,spec}.{js,ts}']
  },
  build: {
    chunkSizeWarningLimit: 1000
  }
});
