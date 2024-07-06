<script lang="ts">
  import { TableBody, TableBodyCell, TableBodyRow, TableHead, TableHeadCell, TableSearch } from 'flowbite-svelte';
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
  }

  function closeModal() {
    showModal = false;
  }

  onMount(getUsers);

  $: filteredUsers = users.filter((user) => user.id !== $authenticatedUser?.id).filter((user) => user.displayName.toLowerCase().indexOf(searchTerm.toLowerCase()) !== -1);
</script>

<Nav />

<div class="mx-auto max-w-4xl p-2">
  <h2 class="mb-4 text-center text-xl font-semibold dark:text-white">{$translations.usersTitle}</h2>

  <TableSearch placeholder="Search by name" hoverable={true} bind:inputValue={searchTerm}>
    <TableHead>
      <TableHeadCell>Name</TableHeadCell>
      <TableHeadCell></TableHeadCell>
    </TableHead>
    <TableBody>
      {#each filteredUsers as user}
        <TableBodyRow>
          <TableBodyCell class="whitespace-normal">{user.displayName}</TableBodyCell>
          <TableBodyCell class="text-right">
            <button on:click={() => editUser(user)} class="mr-4 text-blue-700 dark:text-blue-500">Edit</button>
            <button on:click={() => deleteUser(user)} class="text-blue-700 dark:text-blue-500">Delete</button>
          </TableBodyCell>
        </TableBodyRow>
      {/each}
    </TableBody>
  </TableSearch>
</div>

<UserModal bind:showModal {users} {selectedUser} on:close={closeModal} />
