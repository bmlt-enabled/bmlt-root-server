<script lang="ts">
  import { Button, Checkbox, Helper, P } from 'flowbite-svelte';
  import { createForm } from 'felte';
  import { createEventDispatcher } from 'svelte';
  import { validator } from '@felte/validator-yup';
  import * as yup from 'yup';

  import RootServerApi from '../lib/RootServerApi';
  import type { ServiceBody } from 'bmlt-root-server-client';
  import { spinner } from '../stores/spinner';
  import { translations } from '../stores/localization';

  export let deleteServiceBody: ServiceBody;
  let confirmed = false;
  let errorMessage = '';

  const dispatch = createEventDispatcher<{ deleted: { serviceBodyId: number } }>();

  const { form } = createForm({
    initialValues: { ServiceBodyId: deleteServiceBody?.id, confirmed: false },
    onSubmit: async () => {
      spinner.show();
      await RootServerApi.deleteServiceBody(deleteServiceBody.id);
    },
    onError: async (error) => {
      console.log(error);
      // TODO: Handle Error You cannot delete a service body while other service bodies or meetings are assigned to it.
      await RootServerApi.handleErrors(error as Error);
      spinner.hide();
    },
    onSuccess: () => {
      spinner.hide();
      dispatch('deleted', { serviceBodyId: deleteServiceBody.id });
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
    <P class="mb-5">{$translations.confirmDeleteServiceBody}</P>
    <P class="mb-5">{deleteServiceBody.name}</P>
    <div class="mb-5">
      <Checkbox bind:checked={confirmed} class="justify-center" name="confirmed">{$translations.confirmYesImSure}</Checkbox>
    </div>
    <div class="mb-5">
      <Button type="submit" class="w-full" disabled={!confirmed ? true : null}>{$translations.delete}</Button>
    </div>
    <Helper class="mt-2" color="red">
      {#if errorMessage}
        <P color="text-red-700 dark:text-red-500">{errorMessage}</P>
      {/if}
    </Helper>
  </div>
</form>
