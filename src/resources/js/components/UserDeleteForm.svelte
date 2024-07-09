<script lang="ts">
  import { Button, Checkbox, P } from 'flowbite-svelte';
  import { createForm } from 'felte';
  import { createEventDispatcher } from 'svelte';

  import RootServerApi from '../lib/RootServerApi';
  import type { User } from 'bmlt-root-server-client';
  import { spinner } from '../stores/spinner';
  import { translations } from '../stores/localization';

  export let deleteUser: User;
  let confirmed = false;

  const dispatch = createEventDispatcher<{ deleted: { userId: number } }>();

  const { form } = createForm({
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
    }
  });
</script>

<form use:form>
  <div>
    <P class="mb-5">{$translations.confirmDeleteUser}</P>
    <P class="mb-5">{deleteUser.displayName}</P>
    <div class="mb-5">
      <Checkbox bind:checked={confirmed} class="justify-center" name="confirmed">{$translations.confirmYesImSure}</Checkbox>
    </div>
    <div class="mb-5">
      <Button type="submit" class="w-full" disabled={!confirmed}>{$translations.delete}</Button>
    </div>
  </div>
</form>
