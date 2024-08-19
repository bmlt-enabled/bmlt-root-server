<script lang="ts">
  import { Button, Checkbox, Helper, P } from 'flowbite-svelte';
  import { createForm } from 'felte';
  import { createEventDispatcher } from 'svelte';
  import { validator } from '@felte/validator-yup';
  import * as yup from 'yup';

  import RootServerApi from '../lib/RootServerApi';
  import type { Meeting } from 'bmlt-root-server-client';
  import { spinner } from '../stores/spinner';
  import { translations } from '../stores/localization';

  export let deleteMeeting: Meeting;
  let confirmed = false;
  let errorMessage: string | undefined;

  const dispatch = createEventDispatcher<{ deleted: { meetingId: number } }>();

  const { form } = createForm({
    initialValues: { meetingId: deleteMeeting?.id, confirmed: false },
    onSubmit: async () => {
      spinner.show();
      await RootServerApi.deleteMeeting(deleteMeeting.id);
    },
    onError: async (error) => {
      await RootServerApi.handleErrors(error as Error, {
        handleConflictError: () => {
          confirmed = false;
          errorMessage = 'conflict';
          console.log(error);
        }
      });
      spinner.hide();
    },
    onSuccess: () => {
      spinner.hide();
      dispatch('deleted', { meetingId: deleteMeeting.id });
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
    <P class="mb-5">{$translations.confirmDeleteMeeting}</P>
    <P class="mb-5">{deleteMeeting.name}</P>
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
