<script lang="ts">
  import { Button, P, TableBody, TableBodyCell, TableBodyRow, TableHead, TableHeadCell, TableSearch } from 'flowbite-svelte';
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
  import UserForm from '../components/UserForm.svelte';

  let isLoaded = $state(false);
  let users: User[] = $state([]);
  let filteredUsers: User[] = $state([]);
  let showModal = $state(false);
  let showDeleteModal = $state(false);
  let searchTerm = $state('');
  let selectedUser: User | null = $state(null);
  let deleteUser: User | null = $state(null);

  async function getUsers(): Promise<void> {
    try {
      spinner.show();
      users = await RootServerApi.getUsers();
      isLoaded = true;
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

  function onSaved(user: User) {
    const i = users.findIndex((u) => u.id === user.id);
    if (i === -1) {
      users = [...users, user];
    } else {
      users[i] = user;
    }
    closeModal();
  }

  function onDeleted(user: User) {
    users = users.filter((u) => u.id !== user.id);
    showDeleteModal = false;
  }

  function openModal() {
    showModal = true;
  }

  function closeModal() {
    showModal = false;
  }

  onMount(getUsers);

  $effect(() => {
    filteredUsers = users
      .sort((u1, u2) => u1.displayName.localeCompare(u2.displayName))
      .filter((u) => u.id !== $authenticatedUser?.id)
      .filter((u) => u.displayName.toLowerCase().indexOf(searchTerm.toLowerCase()) !== -1);
  });
</script>

<Nav />

<div class="mx-auto max-w-3xl p-2">
  <h2 class="mb-4 text-center text-xl font-semibold dark:text-white">{$translations.usersTitle}</h2>
  {#if isLoaded}
    {#if users.length > 1}
      <TableSearch placeholder={$translations.searchByName} hoverable={true} bind:inputValue={searchTerm}>
        <TableHead>
          <TableHeadCell colspan={$authenticatedUser?.type === 'admin' ? 2 : 1}>
            {#if $authenticatedUser?.type === 'admin'}
              <div class="flex">
                <div class="mt-2.5 grow">Name</div>
                <div><Button onclick={() => handleAdd()} class="whitespace-nowrap" aria-label={$translations.addUser}>{$translations.addUser}</Button></div>
              </div>
            {:else}
              {$translations.nameTitle}
            {/if}
          </TableHeadCell>
        </TableHead>
        <TableBody>
          {#each filteredUsers as user (user.id)}
            <TableBodyRow onclick={() => handleEdit(user)} class="cursor-pointer" aria-label={$translations.editUser}>
              <TableBodyCell class="whitespace-normal">{user.displayName}</TableBodyCell>
              {#if $authenticatedUser?.type === 'admin'}
                <TableBodyCell class="text-right">
                  <Button color="none" onclick={(e: MouseEvent) => handleDelete(e, user)} class="text-blue-700 dark:text-blue-500" aria-label={$translations.deleteUser + ' ' + user.displayName}>
                    <TrashBinOutline title={{ id: 'deleteUser', title: $translations.deleteUser }} ariaLabel={$translations.deleteUser} />
                  </Button>
                </TableBodyCell>
              {/if}
            </TableBodyRow>
          {/each}
        </TableBody>
      </TableSearch>
    {:else if $authenticatedUser?.type === 'admin'}
      <div class="p-2">
        <UserForm {users} {selectedUser} onSaveSuccess={onSaved} />
      </div>
    {:else}
      <P class="text-center">{$translations.noUsersTitle}</P>
    {/if}
  {/if}
</div>

<UserModal bind:showModal {users} {selectedUser} onSaveSuccess={onSaved} onClose={closeModal} />
{#if deleteUser}
  <UserDeleteModal bind:showDeleteModal {deleteUser} onDeleteSuccess={onDeleted} />
{/if}
