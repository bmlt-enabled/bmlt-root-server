/// <reference types="vitest" />
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/js/App.tsx'],
      refresh: true,
    }),
    react(),
  ],
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: './resources/js/test/setup.ts',
    coverage: {
      reporter: ['text', 'json', 'html'],
    },
  },
});
