<script lang="ts">
  import { TableBody, TableBodyCell, TableBodyRow, TableHead, TableHeadCell, TableSearch } from 'flowbite-svelte';
  import Nav from '../components/NavBar.svelte';
  import UsersModal from '../components/UsersModal.svelte';

  import { authenticatedUser } from '../stores/apiCredentials';
  import { spinner } from '../stores/spinner';
  import { translations } from '../stores/localization';
  import RootServerApi from '../lib/RootServerApi';
  import { onMount } from 'svelte';
  import type { User } from 'bmlt-root-server-client';

  let showModal = false;
  let searchTerm = '';
  let usersById: Record<number, User> = {};
  let userItems = [{ value: -1, name: '', username: '' }];
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
      userItems = users.map((user) => ({ value: user.id, name: user.displayName, username: user.username })).sort((a, b) => a.name.localeCompare(b.name));
      spinner.hide();
    } catch (error: any) {
      RootServerApi.handleErrors(error);
    }
  }

  function editUser(userId: number) {
    selectedUserId = userId;
    showModal = true;
  }

  function closeModal() {
    showModal = false;
  }

  onMount(getUsers);
  $: filteredUsers = userItems.filter((user) => user.name.toLowerCase().indexOf(searchTerm.toLowerCase()) !== -1);
</script>

<Nav />

<div class="mx-auto max-w-xl p-2">
  <h2 class="mb-4 text-center text-xl font-semibold dark:text-white">{$translations.usersTitle}</h2>

  <TableSearch placeholder="Search by name" hoverable={true} bind:inputValue={searchTerm}>
    <TableHead>
      <TableHeadCell>Name</TableHeadCell>
      <TableHeadCell>Username</TableHeadCell>
      <TableHeadCell></TableHeadCell>
    </TableHead>
    <TableBody>
      {#each filteredUsers as user}
        <TableBodyRow>
          <TableBodyCell>{user.name}</TableBodyCell>
          <TableBodyCell>{user.username}</TableBodyCell>
          <TableBodyCell>
            <button on:click={() => editUser(user.value)} class="text-blue-500 hover:underline">Edit</button>
          </TableBodyCell>
        </TableBodyRow>
      {/each}
    </TableBody>
  </TableSearch>
</div>

<UsersModal bind:showModal {selectedUserId} {usersById} on:close={closeModal} />
