import { defineConfig } from 'vitest/config';
import { svelte } from '@sveltejs/vite-plugin-svelte';
import sveltePreprocess from 'svelte-preprocess';

// The 'alias' entry in test is a hack to get onMount to work correctly with unit tests -- without it,
// onMount gets called when showing an actual website but not with unit tests.  The hack is discussed
// here: https://github.com/vitest-dev/vitest/issues/2834
// Search the code base for comments with 'svelte-hack' in them to find all the places where the hack
// can be removed if the vitest people fix this.

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
    alias: [
      {
        find: /svelte\/ssr.js/,
        replacement: "svelte/index.js",
      },
      ]
  },
});
