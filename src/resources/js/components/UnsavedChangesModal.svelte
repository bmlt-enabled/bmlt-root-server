<script lang="ts">
  import { Modal, Button, P } from 'flowbite-svelte';
  import { translations } from '../stores/localization';

  interface Props {
    open: boolean;
    handleCancelClose: () => void;
    handleConfirmClose: () => void;
  }

  let { open = $bindable(), handleCancelClose, handleConfirmClose }: Props = $props();
</script>

<Modal bind:open size="sm" defaultClass="border-4">
  <div class="mb-5">
    <P>{$translations.youHaveUnsavedChanges}</P>
    <div class="mt-4 flex justify-end space-x-2">
      <Button color="alternative" onclick={handleCancelClose}>{$translations.cancel}</Button>
      <Button color="red" onclick={handleConfirmClose}>{$translations.closeWithoutSaving}</Button>
    </div>
  </div>
</Modal>
