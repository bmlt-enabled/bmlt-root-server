// Hack to get onMount to work correctly with unit tests -- see the comment in src/vitest.config.ts
// Search the code base for comments with 'svelte-hack' in them to find all the places where the hack
// can be removed if the vitest people fix this.
declare module 'svelte/internal';
