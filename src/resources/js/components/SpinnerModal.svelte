<script lang="ts">
  import { Modal, Spinner } from 'flowbite-svelte';
  import { spinner } from '../stores/spinner';

  let isActive = $derived(!!$spinner);
</script>

<Modal bind:open={isActive} color="none" class="shadow-none" size="xs" dismissable={false}>
  <div class="text-center">
    <Spinner />
  </div>
</Modal>
