<script lang="ts">
  import { Button, TableBody, TableBodyCell, TableBodyRow, TableHead, TableHeadCell, TableSearch } from 'flowbite-svelte';
  import { TrashBinOutline } from 'flowbite-svelte-icons';
  import Nav from '../components/NavBar.svelte';
  import UserModal from '../components/UserModal.svelte';
  import UserDeleteModal from '../components/UserDeleteModal.svelte';

  import { authenticatedUser } from '../stores/apiCredentials';
  import { spinner } from '../stores/spinner';
  import { translations } from '../stores/localization';
  import RootServerApi from '../lib/RootServerApi';
  import { onMount } from 'svelte';
  import type { User } from 'bmlt-root-server-client';

  let users: User[] = [];
  let filteredUsers: User[] = [];
  let showModal = false;
  let showDeleteModal = false;
  let searchTerm = '';
  let selectedUser: User | null;
  let deleteUser: User;

  async function getUsers(): Promise<void> {
    try {
      spinner.show();
      users = await RootServerApi.getUsers();
    } catch (error: any) {
      await RootServerApi.handleErrors(error);
    } finally {
      spinner.hide();
    }
  }

  function handleAdd() {
    selectedUser = null;
    openModal();
  }

  function handleEdit(user: User) {
    selectedUser = user;
    openModal();
  }

  function handleDelete(event: MouseEvent, user: User) {
    event.stopPropagation();
    deleteUser = user;
    showDeleteModal = true;
  }

  function onSaved(event: CustomEvent) {
    const user = event.detail.user as User;
    const i = users.findIndex((u) => u.id === user.id);
    if (i === -1) {
      users = [...users, user];
    } else {
      users[i] = user;
    }
    closeModal();
  }

  function onDeleted(event: CustomEvent) {
    const userId = event.detail.userId;
    users = users.filter((u) => u.id !== userId);
    showDeleteModal = false;
  }

  function openModal() {
    showModal = true;
  }

  function closeModal() {
    showModal = false;
  }

  onMount(getUsers);

  $: {
    filteredUsers = users
      .sort((u1, u2) => u1.displayName.localeCompare(u2.displayName))
      .filter((u) => u.id !== $authenticatedUser?.id)
      .filter((u) => u.displayName.toLowerCase().indexOf(searchTerm.toLowerCase()) !== -1);
  }
</script>

<Nav />

<div class="mx-auto max-w-3xl p-2">
  <h2 class="mb-4 text-center text-xl font-semibold dark:text-white">{$translations.usersTitle}</h2>
  <TableSearch placeholder={$translations.searchByName} hoverable={true} bind:inputValue={searchTerm}>
    <TableHead>
      <TableHeadCell colspan={$authenticatedUser?.type === 'admin' ? '2' : '1'}>
        {#if $authenticatedUser?.type === 'admin'}
          <div class="flex">
            <div class="mt-2.5 grow">Name</div>
            <div><Button on:click={() => handleAdd()} class="whitespace-nowrap" aria-label={$translations.addUser}>{$translations.addUser}</Button></div>
          </div>
        {:else}
          Name
        {/if}
      </TableHeadCell>
    </TableHead>
    <TableBody>
      {#each filteredUsers as user}
        <TableBodyRow on:click={() => handleEdit(user)} class="cursor-pointer" aria-label={$translations.editUser}>
          <TableBodyCell class="whitespace-normal">{user.displayName}</TableBodyCell>
          {#if $authenticatedUser?.type === 'admin'}
            <TableBodyCell class="text-right">
              <Button color="none" on:click={(e) => handleDelete(e, user)} class="text-blue-700 dark:text-blue-500">
                <TrashBinOutline title={{ id: 'deleteUser', title: $translations.deleteUser }} ariaLabel={$translations.deleteUser} />
              </Button>
            </TableBodyCell>
          {/if}
        </TableBodyRow>
      {/each}
    </TableBody>
  </TableSearch>
</div>

<UserModal bind:showModal {users} {selectedUser} on:saved={onSaved} on:close={closeModal} />
<UserDeleteModal bind:showDeleteModal {deleteUser} on:deleted={onDeleted} />