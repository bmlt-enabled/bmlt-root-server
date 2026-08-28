<script lang="ts">
  import { Button, P, TableBody, TableBodyCell, TableBodyRow, TableHead, TableHeadCell, TableSearch } from 'flowbite-svelte';
  import { TrashBinOutline } from 'flowbite-svelte-icons';
  import { onMount, onDestroy } from 'svelte';

  import type { ServiceBody, User } from 'bmlt-server-client';

  import Nav from '../components/NavBar.svelte';
  import ServiceBodyDeleteModal from '../components/ServiceBodyDeleteModal.svelte';
  import ServiceBodyForm from '../components/ServiceBodyForm.svelte';
  import ServiceBodyModal from '../components/ServiceBodyModal.svelte';
  import DownloadSpreadsheet from '../components/DownloadSpreadsheet.svelte';
  import RootServerApi from '../lib/ServerApi';
  import { authenticatedUser } from '../stores/apiCredentials';
  import { translations } from '../stores/localization';
  import { spinner } from '../stores/spinner';
  import { serviceBodiesState } from '../stores/serviceBodiesState';

  let usersLoaded = $state(false);
  let serviceBodiesLoaded = $state(false);
  let users: User[] = $state([]);
  let serviceBodies: ServiceBody[] = $state([]);
  let showModal = $state(false);
  let showDeleteModal = $state(false);
  let searchTerm = $state('');
  let selectedServiceBody: ServiceBody | null = $state(null);
  let deleteServiceBody: ServiceBody | null = $state(null);
  let lastEditedServiceBodyId: number | null = $state(null);

  async function getUsers(): Promise<void> {
    try {
      spinner.show();
      users = (await RootServerApi.getUsers()).filter((u) => u.id !== $authenticatedUser?.id);
      usersLoaded = true;
    } catch (error: any) {
      await RootServerApi.handleErrors(error);
    } finally {
      spinner.hide();
    }
  }

  async function getServiceBodies(): Promise<void> {
    try {
      spinner.show();
      serviceBodies = await RootServerApi.getServiceBodies();
      lastEditedServiceBodyId = null;
      serviceBodiesLoaded = true;
    } catch (error: any) {
      await RootServerApi.handleErrors(error);
    } finally {
      spinner.hide();
    }
  }

  function handleAdd() {
    selectedServiceBody = null;
    openModal();
  }

  function handleEdit(serviceBody: ServiceBody) {
    selectedServiceBody = serviceBody;
    lastEditedServiceBodyId = serviceBody.id;
    openModal();
  }

  function handleDelete(event: MouseEvent, serviceBody: ServiceBody) {
    event.stopPropagation();
    deleteServiceBody = serviceBody;
    Promise.resolve().then(() => {
      showDeleteModal = true;
    });
  }

  function onSaved(serviceBody: ServiceBody) {
    const i = serviceBodies.findIndex((s) => s.id === serviceBody.id);
    if (i === -1) {
      serviceBodies = [...serviceBodies, serviceBody];
    } else {
      serviceBodies[i] = serviceBody;
    }
    lastEditedServiceBodyId = serviceBody.id;
    closeModal();
  }

  function onDeleted(serviceBody: ServiceBody) {
    serviceBodies = serviceBodies.filter((s) => s.id !== serviceBody.id);
    showDeleteModal = false;
  }

  function openModal() {
    showModal = true;
  }

  function closeModal() {
    showModal = false;
  }

  function isAdminForServiceBody(userId: number, sb: ServiceBody): boolean {
    let s: ServiceBody | undefined = sb;
    while (s) {
      if (s.adminUserId === userId) {
        return true;
      }
      s = serviceBodies.find((x) => x.id === s?.parentId);
    }
    return false;
  }

  onMount(() => {
    // Restore state from store if it exists
    const storedState = $serviceBodiesState;
    if (storedState.serviceBodies.length > 0 && storedState.users.length > 0) {
      serviceBodies = storedState.serviceBodies;
      users = storedState.users;
      searchTerm = storedState.searchTerm;
      lastEditedServiceBodyId = storedState.lastEditedServiceBodyId;
      usersLoaded = true;
      serviceBodiesLoaded = true;
    } else {
      getUsers();
      getServiceBodies();
    }
  });

  onDestroy(() => {
    // Save current state to store when component unmounts
    serviceBodiesState.set({
      serviceBodies,
      users,
      searchTerm,
      lastEditedServiceBodyId
    });
  });

  // Service bodies the authenticated user can edit, ignoring the search term.
  let editableServiceBodies = $derived(
    $authenticatedUser ? serviceBodies.filter((s) => $authenticatedUser?.type === 'admin' || isAdminForServiceBody($authenticatedUser.id, s)).sort((s1, s2) => s1.name.localeCompare(s2.name)) : []
  );

  // The editable service bodies further narrowed by the search term.
  let filteredServiceBodies = $derived(editableServiceBodies.filter((s) => s.name.toLowerCase().indexOf(searchTerm.toLowerCase()) !== -1));

  let csvData = $derived(
    filteredServiceBodies.map((sb) => ({
      id: sb.id,
      name: sb.name,
      description: sb.description,
      type: sb.type,
      adminUserId: sb.adminUserId,
      parentId: sb.parentId,
      worldId: sb.worldId,
      url: sb.url,
      helpline: sb.helpline,
      email: sb.email
    }))
  );
</script>

<Nav />

<div class="mx-auto max-w-3xl p-2">
  <h2 class="mb-4 text-center text-xl font-semibold dark:text-white">{$translations.serviceBodiesTitle}</h2>
  {#if usersLoaded && serviceBodiesLoaded}
    {#if editableServiceBodies.length}
      <TableSearch placeholder={$translations.searchByName} hoverable={true} bind:inputValue={searchTerm}>
        <TableHead>
          <TableHeadCell colspan={$authenticatedUser?.type === 'admin' ? 2 : 1}>
            {#if $authenticatedUser?.type === 'admin'}
              <div class="flex">
                <div class="mt-2.5 grow">Name</div>
                <div class="flex gap-2">
                  <DownloadSpreadsheet data={csvData} filename="service_bodies" />
                  <Button onclick={() => handleAdd()} class="whitespace-nowrap" aria-label={$translations.addServiceBody}>{$translations.addServiceBody}</Button>
                </div>
              </div>
            {:else}
              {$translations.nameTitle}
            {/if}
          </TableHeadCell>
        </TableHead>
        <TableBody>
          {#each filteredServiceBodies as serviceBody (serviceBody.id)}
            <TableBodyRow
              onclick={() => handleEdit(serviceBody)}
              class={`cursor-pointer ${serviceBody.id === lastEditedServiceBodyId ? 'bg-blue-50 dark:bg-blue-900' : ''}`}
              aria-label={$translations.editUser}
            >
              <TableBodyCell class="whitespace-normal">{serviceBody.name}</TableBodyCell>
              {#if $authenticatedUser?.type === 'admin'}
                <TableBodyCell class="text-right">
                  <Button color="alternative" onclick={(e: MouseEvent) => handleDelete(e, serviceBody)} class="border-none text-blue-700 dark:text-blue-500">
                    <TrashBinOutline title={{ id: 'deleteServiceBody', title: $translations.deleteServiceBody }} ariaLabel={$translations.deleteServiceBody + ' ' + serviceBody.name} />
                  </Button>
                </TableBodyCell>
              {/if}
            </TableBodyRow>
          {/each}
        </TableBody>
      </TableSearch>
    {:else if $authenticatedUser?.type === 'admin'}
      <div class="p-2">
        <ServiceBodyForm {serviceBodies} {selectedServiceBody} {users} onSaveSuccess={onSaved} />
      </div>
    {:else}
      <P class="text-center">{$translations.noServiceBodiesTitle}</P>
    {/if}
  {/if}
</div>

{#if showModal}
  <ServiceBodyModal bind:showModal {serviceBodies} {selectedServiceBody} {users} onSaveSuccess={onSaved} onClose={closeModal} />
{/if}
{#if deleteServiceBody}
  <ServiceBodyDeleteModal bind:showDeleteModal {deleteServiceBody} onDeleteSuccess={onDeleted} />
{/if}
