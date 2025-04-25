<script lang="ts">
  import { Button, Checkbox, Helper, P } from 'flowbite-svelte';
  import { createForm } from 'felte';
  import { validator } from '@felte/validator-yup';
  import * as yup from 'yup';

  import RootServerApi from '../lib/RootServerApi';
  import type { User } from 'bmlt-root-server-client';
  import { spinner } from '../stores/spinner';
  import { translations } from '../stores/localization';

  interface Props {
    deleteUser: User;
    onDeleteSuccess?: (user: User) => void; // Callback function prop
  }

  let { deleteUser, onDeleteSuccess }: Props = $props();
  let confirmed = $state(false);
  let errorMessage: string | undefined = $state();

  const { form } = createForm({
    initialValues: { userId: deleteUser?.id, confirmed: false },
    onSubmit: async () => {
      spinner.show();
      await RootServerApi.deleteUser(deleteUser.id);
    },
    onError: async (error) => {
      await RootServerApi.handleErrors(error as Error, {
        handleConflictError: () => {
          confirmed = false;
          errorMessage = $translations.userDeleteConflictError;
        }
      });
      spinner.hide();
    },
    onSuccess: () => {
      spinner.hide();
      onDeleteSuccess?.(deleteUser);
    },
    extend: validator({
      schema: yup.object({
        confirmed: yup.boolean().oneOf([true])
      })
    })
  });
</script>

<form use:form>
  <div>
    <P class="mb-5">{$translations.confirmDeleteUser}</P>
    <P class="mb-5">{deleteUser.displayName}</P>
    <div class="mb-5">
      <Checkbox bind:checked={confirmed} name="confirmed">{$translations.confirmYesImSure}</Checkbox>
      <Helper class="mt-4" color="red">
        {#if errorMessage}
          {errorMessage}
        {/if}
      </Helper>
    </div>
    <div class="mb-5">
      <Button type="submit" class="w-full" disabled={!confirmed ? true : false}>{$translations.delete}</Button>
    </div>
  </div>
</form>
