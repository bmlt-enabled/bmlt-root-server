<script lang="ts">
  import { Modal } from 'flowbite-svelte';

  import type { User } from 'bmlt-root-server-client';
  import UserDeleteForm from './UserDeleteForm.svelte';

  export let showDeleteModal: boolean;
  export let deleteUser: User;
</script>

<Modal bind:open={showDeleteModal} size="sm" outsideclose>
  <div class="p-2">
    <UserDeleteForm {deleteUser} on:deleted />
  </div>
</Modal>
