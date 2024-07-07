<script lang="ts">
  import { TableBody, TableBodyCell, TableBodyRow, TableHead, TableHeadCell, TableSearch } from 'flowbite-svelte';
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
      spinner.hide();
    } catch (error: any) {
      RootServerApi.handleErrors(error);
    }
  }

  function editUser(user: User) {
    selectedUser = user;
    showModal = true;
  }

  function deleteUser(user: User) {
    // TODO
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

  <TableSearch placeholder="Search by name" hoverable={true} bind:inputValue={searchTerm}>
    <TableHead>
      <TableHeadCell>Name</TableHeadCell>
      <TableHeadCell />
    </TableHead>
    <TableBody>
      {#each filteredUsers as user}
        <TableBodyRow class="cursor-pointer" on:click={() => editUser(user)}>
          <TableBodyCell class="whitespace-normal">{user.displayName}</TableBodyCell>
          <TableBodyCell class="text-center">
            <button on:click|stopPropagation={() => deleteUser(user)} class="text-blue-700 dark:text-blue-500"><TrashBinOutline /></button>
          </TableBodyCell>
        </TableBodyRow>
      {/each}
    </TableBody>
  </TableSearch>
</div>

<UserModal bind:showModal {users} {selectedUser} on:close={closeModal} />
