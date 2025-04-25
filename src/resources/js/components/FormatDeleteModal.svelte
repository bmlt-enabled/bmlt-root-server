<script lang="ts">
  import { Modal } from 'flowbite-svelte';

  import type { Format } from 'bmlt-root-server-client';
  import FormatDeleteForm from './FormatDeleteForm.svelte';

  interface Props {
    showDeleteModal: boolean;
    deleteFormat: Format;
    formatName: string;
    onDeleteSuccess?: (format: Format) => void; // Callback function prop
  }

  let { showDeleteModal = $bindable(), deleteFormat, formatName, onDeleteSuccess }: Props = $props();
</script>

<Modal bind:open={showDeleteModal} size="sm" outsideclose>
  <div class="p-2">
    <FormatDeleteForm {deleteFormat} {formatName} {onDeleteSuccess} />
  </div>
</Modal>
