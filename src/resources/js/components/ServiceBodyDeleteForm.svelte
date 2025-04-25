<script lang="ts">
  import { Button, Checkbox, Helper, P } from 'flowbite-svelte';
  import { createForm } from 'felte';
  import { validator } from '@felte/validator-yup';
  import * as yup from 'yup';

  import RootServerApi from '../lib/RootServerApi';
  import type { ServiceBody } from 'bmlt-root-server-client';
  import { spinner } from '../stores/spinner';
  import { translations } from '../stores/localization';

  interface Props {
    deleteServiceBody: ServiceBody;
    onDeleteSuccess?: (serviceBody: ServiceBody) => void; // Callback function prop
  }

  let { deleteServiceBody, onDeleteSuccess }: Props = $props();
  let confirmed = $state(false);
  let errorMessage: string | undefined = $state();

  const { form } = createForm({
    initialValues: { ServiceBodyId: deleteServiceBody?.id, confirmed: false },
    onSubmit: async () => {
      spinner.show();
      await RootServerApi.deleteServiceBody(deleteServiceBody.id);
    },
    onError: async (error) => {
      await RootServerApi.handleErrors(error as Error, {
        handleConflictError: () => {
          confirmed = false;
          errorMessage = $translations.serviceBodyDeleteConflictError;
        }
      });
      spinner.hide();
    },
    onSuccess: () => {
      spinner.hide();
      onDeleteSuccess?.(deleteServiceBody);
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
