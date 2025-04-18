<script lang="ts">
  import { Button, Indicator } from 'flowbite-svelte';
  import type { Snippet } from 'svelte';

  interface Props {
    changeActiveTab: (index: number) => void;
    tabs: string[];
    errorTabs: string[];
    tabsSnippets: Snippet[];
    inactiveClasses?: string;
    activeClasses?: string;
    activeTab?: number;
  }

  let {
    changeActiveTab,
    tabs,
    errorTabs,
    tabsSnippets,
    inactiveClasses = 'p-4 text-primary-600 bg-gray-100 rounded-t-lg dark:bg-gray-700 dark:text-gray-200 px-3.5 py-2.5 text-xs sm:px-5 sm:py-3 sm:text-sm relative',
    activeClasses = 'p-4 text-gray-500 bg-white rounded-t-lg hover:text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white px-3.5 py-2.5 text-xs sm:px-5 sm:py-3 sm:text-sm relative',
    activeTab = 0
  }: Props = $props();

  function setActiveTab(index: number) {
    changeActiveTab(index);
    activeTab = index;
  }
</script>

<div>
  <div class="flex flex-wrap space-x-2 border-b sm:space-x-3">
    {#each tabs as tab, index (index)}
      <Button
        color={activeTab === index ? 'light' : 'dark'}
        onclick={() => setActiveTab(index)}
        class={activeTab === index ? inactiveClasses : activeClasses}
        aria-selected={activeTab === index}
        aria-label={tab}
        role="tab"
      >
        {tab}
        {#if errorTabs.includes(tab)}
          <Indicator color="red" size="sm" placement="top-right" />
        {/if}
      </Button>
    {/each}
  </div>
  <div class="mt-4">
    {#each tabsSnippets as s, i}
      <div class={activeTab === i ? '' : 'hidden'}>
        {@render s()}
      </div>
    {/each}
  </div>
</div>
