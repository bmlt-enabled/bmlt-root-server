<script lang="ts">
  import { Button, Helper, Checkbox } from 'flowbite-svelte';
  import { createForm } from 'felte';
  import { createEventDispatcher } from 'svelte';
  import { ExclamationCircleOutline } from 'flowbite-svelte-icons';
  import { validator } from '@felte/validator-yup';
  import * as yup from 'yup';

  import RootServerApi from '../lib/RootServerApi';
  import type { User } from 'bmlt-root-server-client';
  import { spinner } from '../stores/spinner';
  import { translations } from '../stores/localization';

  export let selectedUser: User | null;

  const dispatch = createEventDispatcher();
  let checkboxChecked: boolean = false;

  const { form, errors } = createForm({
    initialValues: { userId: selectedUser?.id, confirmCheckbox: false },
    onSubmit: async () => {
      spinner.show();
      if (selectedUser) {
        await RootServerApi.deleteUser(selectedUser.id);
        dispatch('userDeleted', { userId: selectedUser.id });
      }
    },
    onError: async (error) => {
      console.log(error);
      spinner.hide();
    },
    onSuccess: () => {
      spinner.hide();
    },
    extend: validator({
      schema: yup.object({
        confirmCheckbox: yup.boolean().oneOf([true], $translations.confirmYouMust)
      })
    })
  });

  function closeModal() {
    dispatch('close');
  }
</script>

<form use:form>
  <div class="text-center">
    <ExclamationCircleOutline class="mx-auto mb-4 h-12 w-12 text-gray-400 dark:text-gray-200" />
    <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">{$translations.confirmAreYouSure}? <br />{selectedUser?.displayName}</h3>
    <div class="mb-5">
      <Checkbox id="confirmCheckbox" name="confirmCheckbox" bind:checked={checkboxChecked}>{$translations.confirmActionCantBeUndone}</Checkbox>
    </div>
    <Button type="submit" color="red" class="me-2" disabled={!checkboxChecked}>{$translations.confirmYes}</Button>
    <Button type="button" on:click={closeModal} color="alternative">{$translations.confirmNo}</Button>
    <Helper class="mt-2" color="red">
      {#if $errors.confirmCheckbox}
        {$errors.confirmCheckbox}
      {/if}
    </Helper>
  </div>
</form>
