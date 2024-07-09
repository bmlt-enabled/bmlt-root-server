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

  export let deleteUser: User;

  const dispatch = createEventDispatcher();

  const { form, errors } = createForm({
    initialValues: { userId: deleteUser?.id, confirmed: false },
    onSubmit: async () => {
      spinner.show();
      await RootServerApi.deleteUser(deleteUser.id);
    },
    onError: async (error) => {
      console.log(error);
      await RootServerApi.handleErrors(error as Error);
      spinner.hide();
    },
    onSuccess: () => {
      spinner.hide();
      dispatch('deleted', { userId: deleteUser.id });
    },
    extend: validator({
      schema: yup.object({
        confirmed: yup.boolean().oneOf([true], $translations.confirmYouMust)
      })
    })
  });
</script>

<form use:form>
  <div class="text-center">
    <ExclamationCircleOutline class="mx-auto mb-4 h-12 w-12 text-gray-400 dark:text-gray-200" />
    <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">{$translations.confirmAreYouSure} <br />{deleteUser?.displayName}</h3>
    <div class="mb-5">
      <Checkbox id="confirmed" name="confirmed">{$translations.confirmActionCantBeUndone}</Checkbox>
    </div>
    <Button type="submit" color="red" class="me-2">{$translations.confirmYes}</Button>
    <Helper class="mt-2" color="red">
      {#if $errors.confirmed}
        {$errors.confirmed}
      {/if}
    </Helper>
  </div>
</form>
