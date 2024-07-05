<script lang="ts">
  import { Label, Select } from 'flowbite-svelte';
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

  async function getUsers(): Promise<void> {
    try {
      spinner.show();
      const users = await RootServerApi.getUsers();

      const _usersById: Record<number, User> = {};
      for (const user of users) {
        _usersById[user.id] = user;
        if ($authenticatedUser?.type === 'admin') {
          if (user.ownerId === null) {
            user.ownerId = $authenticatedUser.id;
          }
        }
      }

      usersById = _usersById;
      userItems = users.map((user) => ({ value: user.id, name: user.displayName })).sort((a, b) => a.name.localeCompare(b.name));
      spinner.hide();
    } catch (error: any) {
      RootServerApi.handleErrors(error);
    }
  }

  onMount(getUsers);
</script>

<Nav />

<div class="mx-auto max-w-xl p-4">
  <h2 class="mb-4 text-center text-xl font-semibold dark:text-white">{$translations.usersTitle}</h2>

  <div class="mb-6">
    <Label for="user" class="mb-2">{$translations.userTitle}</Label>
    <Select id="user" items={userItems} name="user" bind:value={selectedUserId} />
  </div>
  {#if selectedUserId !== -1}
    <UsersForm {selectedUserId} {usersById} />
  {/if}
</div>
