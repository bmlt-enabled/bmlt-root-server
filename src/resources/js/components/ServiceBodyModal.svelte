<script lang="ts">
  import { Modal } from 'flowbite-svelte';
  import { get } from 'svelte/store';
  import type { ServiceBody, User } from 'bmlt-root-server-client';
  import ServiceBodyForm from './ServiceBodyForm.svelte';
  import { isDirty } from '../lib/utils';
  import UnsavedChangesModal from './UnsavedChangesModal.svelte';

  interface Props {
    showModal: boolean;
    selectedServiceBody: ServiceBody | null;
    serviceBodies: ServiceBody[];
    users: User[];
    onSaveSuccess?: (serviceBody: ServiceBody) => void; // Callback function prop
    onClose?: () => void; // Callback function prop
  }

  let { showModal = $bindable(), selectedServiceBody, serviceBodies, users, onSaveSuccess, onClose }: Props = $props();

  let showConfirmModal = $state(false);
  let forceClose = false;

  function handleClose() {
    if (get(isDirty) && !forceClose) {
      showModal = true;
      showConfirmModal = true;
    } else {
      showModal = false;
      forceClose = false;
      if (onClose) onClose();
    }
  }

  function handleConfirmClose() {
    showConfirmModal = false;
    forceClose = true;
    showModal = false;
    if (onClose) onClose();
  }

  function handleCancelClose() {
    showConfirmModal = false;
  }

  function handleOutsideClick(event: MouseEvent) {
    const modalContent = document.querySelector('.modal-content');
    const closeModalButton = document.querySelector('[aria-label*="Close modal"]');
    if ((modalContent && !modalContent.contains(event.target as Node)) || (closeModalButton && closeModalButton.contains(event.target as Node))) {
      handleClose();
    }
  }

  $effect(() => {
    if (showModal) {
      document.addEventListener('mousedown', handleOutsideClick);
    } else {
      document.removeEventListener('mousedown', handleOutsideClick);
    }
  });
</script>

<Modal bind:open={showModal} size="sm" class="modal-content">
  <div class="p-4">
    <ServiceBodyForm {serviceBodies} {selectedServiceBody} {users} {onSaveSuccess} />
  </div>
</Modal>

<UnsavedChangesModal bind:open={showConfirmModal} {handleCancelClose} {handleConfirmClose} />
