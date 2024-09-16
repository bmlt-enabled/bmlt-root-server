<script lang="ts">
  import { Modal } from 'flowbite-svelte';
  import { get } from 'svelte/store';

  import type { Format } from 'bmlt-root-server-client';
  import FormatForm from './FormatForm.svelte';
  import { isDirty } from '../lib/utils';
  import UnsavedChangesModal from './UnsavedChangesModal.svelte';

  export let showModal: boolean;
  export let selectedFormat: Format | null;

  let showConfirmModal = false;
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

  $: {
    if (showModal) {
      document.addEventListener('mousedown', handleOutsideClick);
    } else {
      document.removeEventListener('mousedown', handleOutsideClick);
    }
  }
</script>

<Modal bind:open={showModal} size="sm" class="modal-content">
  <div class="p-4">
    <FormatForm {selectedFormat} on:saved />
  </div>
</Modal>

<UnsavedChangesModal bind:open={showConfirmModal} on:cancel={handleCancelClose} on:confirm={handleConfirmClose} />
