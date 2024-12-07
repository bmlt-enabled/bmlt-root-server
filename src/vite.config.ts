import { defineConfig } from 'vite';
import { svelte } from '@sveltejs/vite-plugin-svelte';
import laravel from 'laravel-vite-plugin';
import { svelteTesting } from '@testing-library/svelte/vite';
import type { InlineConfig } from 'vitest/node';
import type { UserConfig } from 'vite';

// This fixes type issue while enabling us to still use single config
// for testing and not have a separate vitest.config.ts file
interface VitestConfigExport extends UserConfig {
  test: InlineConfig;
}

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
} as VitestConfigExport);
