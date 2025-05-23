<script lang="ts">
  import { Modal } from 'flowbite-svelte';
  import { get } from 'svelte/store';

  import type { Format, Meeting, ServiceBody } from 'bmlt-root-server-client';
  import MeetingEditForm from './MeetingEditForm.svelte';
  import { isDirty } from '../lib/utils';
  import UnsavedChangesModal from './UnsavedChangesModal.svelte';

  interface Props {
    showModal: boolean;
    selectedMeeting: Meeting | null;
    formats: Format[];
    serviceBodies: ServiceBody[];
    onSaved: (meeting: Meeting) => void;
    onClosed: () => void;
    onDeleted: (meeting: Meeting) => void;
  }

  let { showModal = $bindable(), selectedMeeting, formats, serviceBodies, onSaved, onClosed, onDeleted }: Props = $props();
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

  const dialogClass = 'fixed top-0 start-0 end-0 h-[85vh] md:h-[95vh] h-modal md:inset-0 md:h-full z-50 w-full p-4 flex';
  const defaultClass = 'modal-content min-h-[85vh] max-h-[85vh] md:min-h-[95vh] md:max-h-[95vh]';
  const bodyClass = 'p-4 md:p-5 space-y-4 flex-1 overflow-y-auto overscroll-contain';
</script>

<Modal bind:open={showModal} size="md" classDialog={dialogClass} class={defaultClass} classBody={bodyClass}>
  <div class="p-2">
    <MeetingEditForm {selectedMeeting} {serviceBodies} {formats} {onSaved} {onClosed} {onDeleted} />
  </div>
</Modal>

<UnsavedChangesModal bind:open={showConfirmModal} {handleCancelClose} {handleConfirmClose} />
