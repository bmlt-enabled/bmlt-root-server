<script lang="ts">
  import { Modal } from 'flowbite-svelte';

  import type { ServiceBody } from 'bmlt-root-server-client';
  import ServiceBodyDeleteForm from './ServiceBodyDeleteForm.svelte';

  export let showDeleteModal: boolean;
  export let deleteServiceBody: ServiceBody;
</script>

<Modal bind:open={showDeleteModal} size="sm" outsideclose>
  <div class="p-2">
    <ServiceBodyDeleteForm {deleteServiceBody} on:deleted />
  </div>
</Modal>
