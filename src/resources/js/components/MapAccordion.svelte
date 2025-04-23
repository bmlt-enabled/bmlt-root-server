<script lang="ts">
  import type { Snippet } from 'svelte';

  interface Props {
    title?: string;
    children?: Snippet;
    onToggle?: (isOpen: boolean) => void;
  }

  let { title = 'Accordion Title', children, onToggle }: Props = $props();
  let isOpen = $state(false);

  function toggleAccordion() {
    isOpen = !isOpen;
    onToggle?.(isOpen);
  }
</script>

<div class="rounded-none">
  <div
    role="button"
    tabindex="0"
    aria-expanded={isOpen}
    class="mb-2 flex cursor-pointer items-center justify-between rounded px-2 py-2 hover:bg-gray-50 dark:hover:bg-gray-700"
    onclick={toggleAccordion}
    onkeydown={(e) => (e.key === 'Enter' || e.key === ' ' ? toggleAccordion() : null)}
  >
    <!-- svelte-ignore a11y_label_has_associated_control -->
    <label class="block text-sm font-medium text-gray-900 rtl:text-right dark:text-gray-300">{title}</label>
    <svg class={`h-5 w-5 transform transition-transform ${isOpen ? 'rotate-180' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
    </svg>
  </div>

  <div class={`map-container ${isOpen ? 'open' : ''}`}>
    <div class="overflow-hidden">
      {@render children?.()}
    </div>
  </div>
</div>

<style>
  .map-container {
    transition:
      max-height 0.3s ease-in-out,
      opacity 0.3s ease-in-out;
    max-height: 0;
    opacity: 0;
    overflow: hidden;
  }
  .map-container.open {
    max-height: 350px;
    opacity: 1;
  }
</style>
