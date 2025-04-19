<script lang="ts">
  import { run } from 'svelte/legacy';

  import { Modal } from 'flowbite-svelte';
  import { get } from 'svelte/store';

  import type { Format } from 'bmlt-root-server-client';
  import FormatForm from './FormatForm.svelte';
  import { isDirty } from '../lib/utils';
  import UnsavedChangesModal from './UnsavedChangesModal.svelte';

  interface Props {
    showModal: boolean;
    selectedFormat: Format | null;
    reservedFormatKeys: string[];
    onSaveSuccess?: (format: Format) => void; // Callback function prop
  }

  let { showModal = $bindable(), selectedFormat, reservedFormatKeys, onSaveSuccess }: Props = $props();

  let showConfirmModal = $state(false);
  let forceClose = false;

  function handleClose() {
    if (get(isDirty) && !forceClose) {
      showModal = true;
      showConfirmModal = true;
    } else {
      showModal = false;
      forceClose = false;
    }
  }

  function handleConfirmClose() {
    showConfirmModal = false;
    forceClose = true;
    showModal = false;
  }

  function handleCancelClose() {
    showConfirmModal = false;
  }

  function handleOutsideClick(event: MouseEvent) {
    const modalContent = document.querySelector('.modal-content');
    if (modalContent && !modalContent.contains(event.target as Node)) {
      handleClose();
    }
  }

  run(() => {
    if (showModal) {
      document.addEventListener('mousedown', handleOutsideClick);
    } else {
      document.removeEventListener('mousedown', handleOutsideClick);
    }
  });
</script>

<Modal bind:open={showModal} size="sm" class="modal-content">
  <div class="p-4">
    <FormatForm {selectedFormat} {reservedFormatKeys} {onSaveSuccess} />
  </div>
</Modal>

<UnsavedChangesModal bind:open={showConfirmModal} {handleCancelClose} {handleConfirmClose} />
