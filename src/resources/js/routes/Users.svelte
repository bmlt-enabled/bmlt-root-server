<script lang="ts">
  import { Button, TableBody, TableBodyCell, TableBodyRow, TableHead, TableHeadCell, TableSearch } from 'flowbite-svelte';
  import { TrashBinOutline } from 'flowbite-svelte-icons';
  import Nav from '../components/NavBar.svelte';
  import UserModal from '../components/UserModal.svelte';

  import { authenticatedUser } from '../stores/apiCredentials';
  import { spinner } from '../stores/spinner';
  import { translations } from '../stores/localization';
  import RootServerApi from '../lib/RootServerApi';
  import { onMount } from 'svelte';
  import type { User } from 'bmlt-root-server-client';

  let users: User[] = [];
  let showModal = false;
  let searchTerm = '';
  let selectedUser: User;

  async function getUsers(): Promise<void> {
    try {
      spinner.show();
      users = (await RootServerApi.getUsers()).sort((a, b) => a.displayName.localeCompare(b.displayName));
    } catch (error: any) {
      RootServerApi.handleErrors(error);
    } finally {
      spinner.hide();
    }
  }

  function addUser() {
    // TODO
    console.log('add');
  }

  function editUser(user: User) {
    selectedUser = user;
    showModal = true;
  }

  function deleteUser(event: MouseEvent, user: User) {
    // TODO
    event.stopPropagation();
    selectedUser = user;
    console.log('delete');
  }

  function closeModal() {
    showModal = false;
  }

  onMount(getUsers);

  $: filteredUsers = users.filter((user) => user.id !== $authenticatedUser?.id).filter((user) => user.displayName.toLowerCase().indexOf(searchTerm.toLowerCase()) !== -1);
</script>

<Nav />

<div class="mx-auto max-w-3xl p-2">
  <h2 class="mb-4 text-center text-xl font-semibold dark:text-white">{$translations.usersTitle}</h2>
  <TableSearch placeholder={$translations.searchByName} hoverable={true} bind:inputValue={searchTerm}>
    <TableHead>
      <TableHeadCell>Name</TableHeadCell>
      {#if $authenticatedUser?.type === 'admin'}
        <TableHeadCell class="pl-0 text-center">
          <Button on:click={() => addUser()} class="w-full whitespace-nowrap" aria-label={$translations.addUser}>{$translations.addUser}</Button>
        </TableHeadCell>
      {/if}
    </TableHead>
    <TableBody>
      {#each filteredUsers as user}
        <TableBodyRow on:click={() => editUser(user)} class="cursor-pointer" aria-label={$translations.editUser}>
          <TableBodyCell class="whitespace-normal">{user.displayName}</TableBodyCell>
          {#if $authenticatedUser?.type === 'admin'}
            <TableBodyCell class="pl-0 text-center">
              <Button color="none" on:click={(e) => deleteUser(e, user)} class="text-blue-700 dark:text-blue-500">
                <TrashBinOutline title={{ id: 'deleteUser', title: $translations.deleteUser }} ariaLabel={$translations.deleteUser} />
              </Button>
            </TableBodyCell>
          {/if}
        </TableBodyRow>
      {/each}
    </TableBody>
  </TableSearch>
</div>

<UserModal bind:showModal {users} {selectedUser} on:close={closeModal} />
