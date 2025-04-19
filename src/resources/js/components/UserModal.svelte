<script lang="ts">
  import { Modal } from 'flowbite-svelte';
  import { get } from 'svelte/store';

  import type { User } from 'bmlt-root-server-client';
  import UserForm from './UserForm.svelte';
  import { isDirty } from '../lib/utils';
  import UnsavedChangesModal from './UnsavedChangesModal.svelte';

  interface Props {
    showModal: boolean;
    selectedUser: User | null;
    users: User[];
    onSaveSuccess?: (user: User) => void; // Callback function prop
  }

  let { showModal = $bindable(), selectedUser, users, onSaveSuccess }: Props = $props();

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
  <div class="p-2">
    <UserForm {users} {selectedUser} {onSaveSuccess} />
  </div>
</Modal>

<UnsavedChangesModal bind:open={showConfirmModal} {handleCancelClose} {handleConfirmClose} />
