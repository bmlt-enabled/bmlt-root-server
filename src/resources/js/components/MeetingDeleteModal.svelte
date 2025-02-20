<script lang="ts">
  import { Modal } from 'flowbite-svelte';

  import type { Meeting } from 'bmlt-root-server-client';
  import MeetingDeleteForm from './MeetingDeleteForm.svelte';

  interface Props {
    showDeleteModal: boolean;
    meetingToDelete: Meeting;
    onDeleted: (meeting: Meeting) => void;
  }

  let { showDeleteModal = $bindable(), meetingToDelete, onDeleted }: Props = $props();
</script>

<Modal bind:open={showDeleteModal} size="sm" outsideclose>
  <div class="p-2">
    <MeetingDeleteForm {meetingToDelete} {onDeleted} />
  </div>
</Modal>
