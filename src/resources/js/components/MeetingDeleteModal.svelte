<script lang="ts">
  import { Modal } from 'flowbite-svelte';

  import type { Meeting } from 'bmlt-root-server-client';
  import MeetingDeleteForm from './MeetingDeleteForm.svelte';

  interface Props {
    showDeleteModal: boolean;
    deleteMeeting: Meeting;
    onDeleted: (meetingID: number) => void;
  }

  let { showDeleteModal = $bindable(), deleteMeeting, onDeleted }: Props = $props();
</script>

<Modal bind:open={showDeleteModal} size="sm" outsideclose>
  <div class="p-2">
    <MeetingDeleteForm {deleteMeeting} {onDeleted} />
  </div>
</Modal>
