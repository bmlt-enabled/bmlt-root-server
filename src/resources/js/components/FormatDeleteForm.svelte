<script lang="ts">
  import { Button, Checkbox, Helper, P } from 'flowbite-svelte';
  import { createForm } from 'felte';
  import { validator } from '@felte/validator-yup';
  import * as yup from 'yup';

  import RootServerApi from '../lib/RootServerApi';
  import type { Format } from 'bmlt-root-server-client';
  import { spinner } from '../stores/spinner';
  import { translations } from '../stores/localization';

  interface Props {
    deleteFormat: Format;
    formatName: string;
    onDeleteSuccess?: (format: Format) => void; // Callback function prop
  }

  let { deleteFormat, formatName, onDeleteSuccess }: Props = $props();
  let confirmed = $state(false);
  let errorMessage: string | undefined = $state();

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
      onDeleteSuccess?.(deleteFormat);
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
      <Button type="submit" class="w-full" disabled={!confirmed}>{$translations.delete}</Button>
    </div>
  </div>
</form>
