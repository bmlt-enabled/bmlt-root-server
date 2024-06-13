import { svelte } from '@sveltejs/vite-plugin-svelte';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
  plugins: [
    svelte(),
    laravel({
      input: ['resources/js/app.ts'],
      refresh: true
    })
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'src')
    }
  },
  server: {
    cors: true,
    fs: {
      allow: [path.resolve(__dirname, 'src'), path.resolve(__dirname, 'node_modules'), 'resources/js']
    }
  }
});
