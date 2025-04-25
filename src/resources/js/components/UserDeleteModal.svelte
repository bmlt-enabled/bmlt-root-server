<script lang="ts">
  import { Modal } from 'flowbite-svelte';

  import type { User } from 'bmlt-root-server-client';
  import UserDeleteForm from './UserDeleteForm.svelte';

  interface Props {
    showDeleteModal: boolean;
    deleteUser: User;
    onDeleteSuccess?: (user: User) => void; // Callback function prop
  }

  let { showDeleteModal = $bindable(), deleteUser, onDeleteSuccess }: Props = $props();
</script>

<Modal bind:open={showDeleteModal} size="sm" outsideclose>
  <div class="p-2">
    <UserDeleteForm {deleteUser} {onDeleteSuccess} />
  </div>
</Modal>
