<script lang="ts">
  import { Modal } from 'flowbite-svelte';

  import type { User } from 'bmlt-root-server-client';
  import UserForm from './UserForm.svelte';

  export let showModal: boolean;
  export let selectedUser: User;
  export let users: User[];
</script>

<Modal bind:open={showModal} size="sm" outsideclose>
  <div class="p-2">
    <UserForm {users} {selectedUser} on:success={() => (showModal = false)} />
  </div>
</Modal>
