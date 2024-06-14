import { defineConfig } from 'vite';
import { svelte } from '@sveltejs/vite-plugin-svelte';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
  plugins: [
    svelte(),
    laravel({
      input: ['resources/js/app.ts'],
      refresh: true
    })
  ]
});
