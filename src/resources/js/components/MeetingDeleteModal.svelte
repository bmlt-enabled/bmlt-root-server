<script lang="ts">
  import { Modal } from 'flowbite-svelte';

  import type { Meeting } from 'bmlt-root-server-client';
  import MeetingDeleteForm from './MeetingDeleteForm.svelte';

  interface Props {
    showDeleteModal: boolean;
    deleteMeeting: Meeting;
  }

  let { showDeleteModal = $bindable(), deleteMeeting }: Props = $props();
</script>

<Modal bind:open={showDeleteModal} size="sm" outsideclose>
  <div class="p-2">
    <MeetingDeleteForm {deleteMeeting} on:deleted />
  </div>
</Modal>
