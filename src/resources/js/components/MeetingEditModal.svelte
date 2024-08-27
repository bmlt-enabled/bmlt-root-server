<script lang="ts">
  import { Modal } from 'flowbite-svelte';
  import { get } from 'svelte/store';
  import { createEventDispatcher } from 'svelte';

  import type { Format, Meeting, ServiceBody } from 'bmlt-root-server-client';
  import MeetingEditForm from './MeetingEditForm.svelte';
  import { isDirty } from '../lib/utils';
  import UnsavedChangesModal from './UnsavedChangesModal.svelte';

  export let showModal: boolean;
  export let selectedMeeting: Meeting | null;
  export let formats: Format[];
  export let serviceBodies: ServiceBody[];

  const dispatch = createEventDispatcher<{
    deleted: { meetingId: number };
    saved: { meeting: Meeting };
  }>();
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

  function onSaved(event: CustomEvent<{ meeting: Meeting }>) {
    dispatch('saved', { meeting: event.detail.meeting });
  }

  function onDeleted(event: CustomEvent<{ meetingId: number }>) {
    dispatch('deleted', { meetingId: event.detail.meetingId });
  }
</script>

<Modal bind:open={showModal} size="md">
  <div class="modal-content p-2">
    <MeetingEditForm {selectedMeeting} {serviceBodies} {formats} on:saved={onSaved} on:deleted={onDeleted} />
  </div>
</Modal>

<UnsavedChangesModal bind:open={showConfirmModal} on:cancel={handleCancelClose} on:confirm={handleConfirmClose} />
