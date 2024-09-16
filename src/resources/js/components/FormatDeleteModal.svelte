<script lang="ts">
  import { Modal } from 'flowbite-svelte';

  import type { Format } from 'bmlt-root-server-client';
  import FormatDeleteForm from './FormatDeleteForm.svelte';

  export let showDeleteModal: boolean;
  export let deleteFormat: Format;
  export let formatName: string;
</script>

<Modal bind:open={showDeleteModal} size="sm" outsideclose>
  <div class="p-2">
    <FormatDeleteForm {deleteFormat} {formatName} on:deleted />
  </div>
</Modal>
