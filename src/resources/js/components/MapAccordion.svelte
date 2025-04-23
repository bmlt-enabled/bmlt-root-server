<script lang="ts">
  import L from 'leaflet';
  interface Props {
    title?: string;
    map: google.maps.Map | L.Map | undefined;
    children?: import('svelte').Snippet;
  }

  let { title = 'Accordion Title', map, children }: Props = $props();
  let isOpen = $state(false);

  function isLeafletMap(map: google.maps.Map | L.Map | null): map is L.Map {
    return typeof (map as L.Map).invalidateSize === 'function';
  }

  function toggleAccordion() {
    isOpen = !isOpen;
    if (isOpen && map) {
      setTimeout(() => {
        if (isLeafletMap(map)) {
          map.invalidateSize();
        }
      }, 300); // length of transition delay
    }
  }
</script>

<div class="rounded-none">
  <div
    role="button"
    tabindex="0"
    aria-expanded={isOpen}
    class={`flex cursor-pointer items-center justify-between py-2 ${isOpen ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400'}`}
    onclick={toggleAccordion}
    onkeydown={(e) => (e.key === 'Enter' || e.key === ' ' ? toggleAccordion() : null)}
  >
    <!-- svelte-ignore a11y_label_has_associated_control -->
    <label class="block text-sm font-medium rtl:text-right">{title}</label>
    <svg class={`h-5 w-5 transform transition-transform ${isOpen ? 'rotate-180' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
    </svg>
  </div>
  <div class="overflow-hidden transition-all duration-300" style="max-height: {isOpen ? '500px' : '0'}">
    <div id="locationMap" class="transition-all duration-300" style="height: {isOpen ? '500px' : '0'}">
      {@render children?.()}
    </div>
  </div>
</div>
