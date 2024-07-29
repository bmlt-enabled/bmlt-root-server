<script lang="ts">
  import { Modal } from 'flowbite-svelte';
  import { get } from 'svelte/store';

  import type { User } from 'bmlt-root-server-client';
  import UserForm from './UserForm.svelte';
  import { isDirty } from '../lib/utils';
  import UnsavedChangesModal from './UnsavedChangesModal.svelte';

  export let showModal: boolean;
  export let selectedUser: User | null;
  export let users: User[];

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

<Modal bind:open={showModal} size="sm">
  <div class="modal-content p-2">
    <UserForm {users} {selectedUser} on:saved />
  </div>
</Modal>

<UnsavedChangesModal bind:open={showConfirmModal} on:cancel={handleCancelClose} on:confirm={handleConfirmClose} />
