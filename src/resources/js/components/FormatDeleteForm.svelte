<script lang="ts">
  import { Button, Checkbox, Helper, P } from 'flowbite-svelte';
  import { createForm } from 'felte';
  import { createEventDispatcher } from 'svelte';
  import { validator } from '@felte/validator-yup';
  import * as yup from 'yup';

  import RootServerApi from '../lib/RootServerApi';
  import type { Format } from 'bmlt-root-server-client';
  import { spinner } from '../stores/spinner';
  import { translations } from '../stores/localization';

  export let deleteFormat: Format;
  export let formatName: string;
  let confirmed = false;
  let errorMessage: string | undefined;

  const dispatch = createEventDispatcher<{ deleted: { formatId: number } }>();

  const { form } = createForm({
    initialValues: { formatId: deleteFormat?.id, confirmed: false },
    onSubmit: async () => {
      spinner.show();
      await RootServerApi.deleteFormat(deleteFormat.id);
    },
    onError: async (error) => {
      await RootServerApi.handleErrors(error as Error, {
        handleConflictError: () => {
          confirmed = false;
          errorMessage = $translations.formatDeleteConflictError;
        },
        handleValidationError: () => {
          confirmed = false;
          errorMessage = $translations.formatValidationError;
        }
      });
      spinner.hide();
    },
    onSuccess: () => {
      spinner.hide();
      dispatch('deleted', { formatId: deleteFormat.id });
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
    <P class="mb-5">{$translations.confirmDeleteFormat}</P>
    <P class="mb-5">{formatName}</P>
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
