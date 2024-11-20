<script lang="ts">
  export let header: string;
  export let open: boolean = false;
  export let label: string = 'Toggle accordion';

  function accordionToggle() {
    open = !open;
  }
</script>

<div class="mb-2 w-full rounded-md border border-gray-700 bg-gray-800">
  <button
    type="button"
    aria-expanded={open}
    aria-label={label}
    class="flex w-full items-center justify-between rounded-md px-4 py-4 text-left text-sm font-semibold text-gray-300 hover:bg-gray-700 focus:outline-none"
    on:click={accordionToggle}
    on:keydown={(e) => (e.key === 'Enter' || e.key === ' ') && accordionToggle()}
  >
    <span class="w-full">{header}</span>

    {#if open}
      <svg class="h-3 w-3 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5 5 1 1 5" />
      </svg>
    {:else}
      <svg class="h-3 w-3 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
      </svg>
    {/if}
  </button>

  <div class={open ? 'w-full bg-gray-800 px-4 py-2 text-gray-200' : 'hidden'}>
    <slot />
  </div>

  <div class="w-full border-t border-gray-700"></div>
</div>
