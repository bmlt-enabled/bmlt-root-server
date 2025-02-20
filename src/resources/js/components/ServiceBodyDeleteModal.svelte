<script lang="ts">
  import { Modal } from 'flowbite-svelte';

  import type { ServiceBody } from 'bmlt-root-server-client';
  import ServiceBodyDeleteForm from './ServiceBodyDeleteForm.svelte';

  interface Props {
    showDeleteModal: boolean;
    deleteServiceBody: ServiceBody;
    onDeleteSuccess?: (serviceBody: ServiceBody) => void; // Callback function prop
  }

  let { showDeleteModal = $bindable(), deleteServiceBody, onDeleteSuccess }: Props = $props();
</script>

<Modal bind:open={showDeleteModal} size="sm" outsideclose>
  <div class="p-2">
    <ServiceBodyDeleteForm {deleteServiceBody} {onDeleteSuccess} />
  </div>
</Modal>
