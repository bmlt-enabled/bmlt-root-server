<script lang="ts">
  import { createEventDispatcher } from 'svelte';
  import { Modal, Button, P } from 'flowbite-svelte';
  import { translations } from '../stores/localization';

  interface Props {
    open: boolean;
  }

  let { open = $bindable() }: Props = $props();

  const dispatch = createEventDispatcher();

  function handleCancelClose() {
    dispatch('cancel');
  }

  function handleConfirmClose() {
    dispatch('confirm');
  }
</script>

<Modal bind:open size="sm" defaultClass="border-4">
  <div class="mb-5">
    <P>{$translations.youHaveUnsavedChanges}</P>
    <div class="mt-4 flex justify-end space-x-2">
      <Button color="alternative" on:click={handleCancelClose}>{$translations.cancel}</Button>
      <Button color="red" on:click={handleConfirmClose}>{$translations.closeWithoutSaving}</Button>
    </div>
  </div>
</Modal>
