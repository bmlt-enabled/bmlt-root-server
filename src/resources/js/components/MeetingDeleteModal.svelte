<script lang="ts">
  import { Modal } from 'flowbite-svelte';

  import type { Meeting } from 'bmlt-root-server-client';
  import MeetingDeleteForm from './MeetingDeleteForm.svelte';

  export let showDeleteModal: boolean;
  export let deleteMeeting: Meeting;
</script>

<Modal bind:open={showDeleteModal} size="sm" outsideclose>
  <div class="p-2">
    <MeetingDeleteForm {deleteMeeting} on:deleted />
  </div>
</Modal>
