<script lang="ts">
  import { Button, Checkbox, Helper, P } from 'flowbite-svelte';
  import { createForm } from 'felte';
  import { validator } from '@felte/validator-yup';
  import * as yup from 'yup';

  import RootServerApi from '../lib/RootServerApi';
  import type { Meeting } from 'bmlt-root-server-client';
  import { spinner } from '../stores/spinner';
  import { translations } from '../stores/localization';

  interface Props {
    meetingToDelete: Meeting;
    onDeleted: (meeting: Meeting) => void;
  }

  let { meetingToDelete, onDeleted }: Props = $props();
  let confirmed = $state(false);
  let errorMessage: string | undefined = $state();

  const { form } = createForm({
    initialValues: { meetingId: meetingToDelete?.id, confirmed: false },
    onSubmit: async () => {
      spinner.show();
      await RootServerApi.deleteMeeting(meetingToDelete.id);
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
      onDeleted(meetingToDelete);
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
    <P class="mb-5">{meetingToDelete.name}</P>
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
