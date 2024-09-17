<script lang="ts">
  import { Button } from 'flowbite-svelte';

  export let tabs: string[] = [];
  let activeTab: number = 0;

  function setActiveTab(index: number) {
    activeTab = index;
  }
</script>

<div>
  <div class="flex space-x-4 border-b">
    {#each tabs as tab, index}
      <Button color={activeTab === index ? 'light' : 'dark'} on:click={() => setActiveTab(index)} class="py-2" aria-selected={activeTab === index} role="tab">
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

<style>
  .hidden {
    display: none;
  }
</style>
