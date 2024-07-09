<script lang="ts">
  import { Modal } from 'flowbite-svelte';

  import type { User } from 'bmlt-root-server-client';
  import UserDeleteForm from './UserDeleteForm.svelte';

  export let showUserDeleteModal: boolean;
  export let selectedUser: User | null;

  function closeModal() {
    showUserDeleteModal = false;
  }
</script>

<Modal bind:open={showUserDeleteModal} size="sm" outsideclose>
  <div class="p-2">
    <UserDeleteForm {selectedUser} on:userDeleted on:close={closeModal} />
  </div>
</Modal>
