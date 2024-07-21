<script lang="ts">
  import { Modal } from 'flowbite-svelte';

  import type { ServiceBody, User } from 'bmlt-root-server-client';
  import ServiceBodyForm from './ServiceBodyForm.svelte';

  export let showModal: boolean;
  export let selectedServiceBody: ServiceBody | null;
  export let serviceBodies: ServiceBody[];
  export let users: User[];
</script>

<Modal bind:open={showModal} size="sm" outsideclose>
  <div class="p-2">
    <ServiceBodyForm {serviceBodies} {selectedServiceBody} {users} on:saved />
  </div>
</Modal>
