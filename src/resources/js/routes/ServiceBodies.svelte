<script lang="ts">
  import { Button, P, TableBody, TableBodyCell, TableBodyRow, TableHead, TableHeadCell, TableSearch } from 'flowbite-svelte';
  import { TrashBinOutline } from 'flowbite-svelte-icons';
  // 'svelte-hack' -- import hacked to get onMount to work correctly for unit tests
  import { onMount } from 'svelte/internal';

  import type { ServiceBody, User } from 'bmlt-root-server-client';

  import Nav from '../components/NavBar.svelte';
  import ServiceBodyDeleteModal from '../components/ServiceBodyDeleteModal.svelte';
  import ServiceBodyForm from '../components/ServiceBodyForm.svelte';
  import ServiceBodyModal from '../components/ServiceBodyModal.svelte';
  import RootServerApi from '../lib/RootServerApi';
  import { authenticatedUser } from '../stores/apiCredentials';
  import { translations } from '../stores/localization';
  import { spinner } from '../stores/spinner';

  let usersLoaded = false;
  let serviceBodiesLoaded = false;
  let users: User[] = [];
  let serviceBodies: ServiceBody[] = [];
  let filteredServiceBodies: ServiceBody[] = [];
  let showModal = false;
  let showDeleteModal = false;
  let searchTerm = '';
  let selectedServiceBody: ServiceBody | null;
  let deleteServiceBody: ServiceBody;

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
    openModal();
  }

  function handleDelete(event: MouseEvent, serviceBody: ServiceBody) {
    event.stopPropagation();
    deleteServiceBody = serviceBody;
    showDeleteModal = true;
  }

  function onSaved(event: CustomEvent<{ serviceBody: ServiceBody }>) {
    const serviceBody = event.detail.serviceBody;
    const i = serviceBodies.findIndex((s) => s.id === serviceBody.id);
    if (i === -1) {
      serviceBodies = [...serviceBodies, serviceBody];
    } else {
      serviceBodies[i] = serviceBody;
    }
    closeModal();
  }

  function onDeleted(event: CustomEvent<{ serviceBodyId: number }>) {
    serviceBodies = serviceBodies.filter((s) => s.id !== event.detail.serviceBodyId);
    showDeleteModal = false;
  }

  function openModal() {
    showModal = true;
  }

  function closeModal() {
    showModal = false;
  }

  onMount(() => {
    getUsers();
    getServiceBodies();
  });

  $: {
    // prettier-ignore
    filteredServiceBodies = serviceBodies
      .sort((s1, s2) => s1.name.localeCompare(s2.name))
      .filter((s) => s.name.toLowerCase().indexOf(searchTerm.toLowerCase()) !== -1);
  }
</script>

<Nav />

<div class="mx-auto max-w-3xl p-2">
  <h2 class="mb-4 text-center text-xl font-semibold dark:text-white">{$translations.serviceBodiesTitle}</h2>
  {#if usersLoaded && serviceBodiesLoaded}
    {#if serviceBodies.length}
      <TableSearch placeholder={$translations.searchByName} hoverable={true} bind:inputValue={searchTerm}>
        <TableHead>
          <TableHeadCell colspan={$authenticatedUser?.type === 'admin' ? '2' : '1'}>
            {#if $authenticatedUser?.type === 'admin'}
              <div class="flex">
                <div class="mt-2.5 grow">Name</div>
                <div>
                  <Button on:click={() => handleAdd()} class="whitespace-nowrap" aria-label={$translations.addServiceBody}>{$translations.addServiceBody}</Button>
                </div>
              </div>
            {:else}
              {$translations.nameTitle}
            {/if}
          </TableHeadCell>
        </TableHead>
        <TableBody>
          {#each filteredServiceBodies as serviceBody}
            <TableBodyRow on:click={() => handleEdit(serviceBody)} class="cursor-pointer" aria-label={$translations.editUser}>
              <TableBodyCell class="whitespace-normal">{serviceBody.name}</TableBodyCell>
              {#if $authenticatedUser?.type === 'admin'}
                <TableBodyCell class="text-right">
                  <Button color="none" on:click={(e) => handleDelete(e, serviceBody)} class="text-blue-700 dark:text-blue-500">
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
        <ServiceBodyForm {serviceBodies} {selectedServiceBody} {users} on:saved={onSaved} />
      </div>
    {:else}
      <P class="text-center">{$translations.noServiceBodiesTitle}</P>
    {/if}
  {/if}
</div>

<ServiceBodyModal bind:showModal {serviceBodies} {selectedServiceBody} {users} on:saved={onSaved} on:close={closeModal} />
<ServiceBodyDeleteModal bind:showDeleteModal {deleteServiceBody} on:deleted={onDeleted} />
