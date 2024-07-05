<script lang="ts">
  import { Label, P, Select } from 'flowbite-svelte';
  import Nav from '../components/NavBar.svelte';
  import UsersForm from '../components/UsersForm.svelte';

  import { authenticatedUser } from '../stores/apiCredentials';
  import { spinner } from '../stores/spinner';
  import { translations } from '../stores/localization';
  import RootServerApi from '../lib/RootServerApi';
  import { onMount } from 'svelte';
  import type { User } from 'bmlt-root-server-client';

  let usersById: Record<number, User> = {};
  let userItems = [{ value: -1, name: '' }];
  let selectedUserId = -1;
  let errorMessage = '';

  async function getUsers(): Promise<void> {
    try {
      spinner.show();
      const users = await RootServerApi.getUsers();

      const _usersById: Record<number, User> = {};
      for (const user of users) {
        _usersById[user.id] = user;
        if ($authenticatedUser?.type === 'admin') {
          if (user.ownerId === null) {
            user.ownerId = $authenticatedUser.id.toString();
          }
        }
      }

      usersById = _usersById;
      userItems = users.map((user) => ({ value: user.id, name: user.displayName })).sort((a, b) => a.name.localeCompare(b.name));
      spinner.hide();
    } catch (error: any) {
      // If this happens, it's basically a fatal error. We should implement the default error
      // handler in RootServerApi.handleErrors so that it pops up an error modal rather than
      // implementing a custom error handler on each page. If we need different error handling
      // we can easily stub in a custom error handler later.
      RootServerApi.handleErrors(error, {
        handleError: (error) => {
          errorMessage = error.message;
        }
      });
    }
  }

  onMount(getUsers);
</script>

<Nav />

<div class="mx-auto max-w-xl p-4">
  <h2 class="mb-4 text-center text-xl font-semibold dark:text-white">{$translations.userTitle}</h2>

  <div class="mb-6">
    <Label for="user" class="mb-2">{$translations.userTitle}</Label>
    <Select id="user" items={userItems} name="user" bind:value={selectedUserId} />
  </div>
  <UsersForm {selectedUserId} {usersById} {userItems} />
  {#if errorMessage}
    <div class="mb-4">
      <P color="text-red-700 dark:text-red-500">{errorMessage}</P>
    </div>
  {/if}
</div>
