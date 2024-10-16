<script lang="ts">
  import { Button } from 'flowbite-svelte';

  export let tabs: string[] = [];
  export let inactiveClasses: string = 'p-4 text-primary-600 bg-gray-100 rounded-t-lg dark:bg-gray-700 dark:text-gray-200 px-3.5 py-2.5 text-xs sm:px-5 sm:py-3 sm:text-sm';
  export let activeClasses: string =
    'p-4 text-gray-500 bg-white rounded-t-lg hover:text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white px-3.5 py-2.5 text-xs sm:px-5 sm:py-3 sm:text-sm';

  let activeTab: number = 0;

  function setActiveTab(index: number) {
    activeTab = index;
  }
</script>

<div>
  <div class="flex flex-wrap space-x-2 sm:space-x-3 border-b">
    {#each tabs as tab, index}
      <Button
        color={activeTab === index ? 'light' : 'dark'}
        on:click={() => setActiveTab(index)}
        class={activeTab === index ? inactiveClasses : activeClasses}
        aria-selected={activeTab === index}
        aria-label={tab}
        role="tab"
      >
        {tab}
      </Button>
    {/each}
  </div>
  <div class="mt-4">
    <div class={activeTab === 0 ? '' : 'hidden'}>
      <slot name="tab-content-0" />
    </div>
    <div class={activeTab === 1 ? '' : 'hidden'}>
      <slot name="tab-content-1" />
    </div>
    <div class={activeTab === 2 ? '' : 'hidden'}>
      <slot name="tab-content-2" />
    </div>
    <div class={activeTab === 3 ? '' : 'hidden'}>
      <slot name="tab-content-3" />
    </div>
  </div>
</div>
