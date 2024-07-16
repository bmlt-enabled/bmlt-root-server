<script lang="ts">
  import { Button, P, TableBody, TableBodyCell, TableBodyRow, TableHead, TableHeadCell, TableSearch } from 'flowbite-svelte';
  import { TrashBinOutline } from 'flowbite-svelte-icons';
  import Nav from '../components/NavBar.svelte';
  import ServiceBodyModal from '../components/ServiceBodyModal.svelte';
  import ServiceBodyForm from '../components/ServiceBodyForm.svelte';

  import { authenticatedUser } from '../stores/apiCredentials';
  import { spinner } from '../stores/spinner';
  import { translations } from '../stores/localization';
  import RootServerApi from '../lib/RootServerApi';
  // svelte-hack' -- import hacked to get onMount to work correctly for unit tests
  import { onMount } from 'svelte/internal';
  import type { ServiceBody } from 'bmlt-root-server-client';
  import ServiceBodyDeleteModal from '../components/ServiceBodyDeleteModal.svelte';

  let isLoaded = false;
  let serviceBodies: ServiceBody[] = [];
  let filteredServiceBodies: ServiceBody[] = [];
  let showModal = false;
  let showDeleteModal = false;
  let searchTerm = '';
  let selectedServiceBody: ServiceBody | null;
  let deleteServiceBody: ServiceBody;

  async function getServiceBodies(): Promise<void> {
    try {
      spinner.show();
      serviceBodies = await RootServerApi.getServiceBodies();
      isLoaded = true;
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
    console.log(serviceBody);
    openModal();
  }

  function handleDelete(event: MouseEvent, serviceBody: ServiceBody) {
    event.stopPropagation();
    deleteServiceBody = serviceBody;
    showDeleteModal = true;
    console.log(serviceBody);
  }

  function onSaved(event: CustomEvent<{ serviceBody: ServiceBody }>) {
    const serviceBody = event.detail.serviceBody;
    const i = serviceBodies.findIndex((u) => u.id === serviceBody.id);
    if (i === -1) {
      serviceBodies = [...serviceBodies, serviceBody];
    } else {
      serviceBodies[i] = serviceBody;
    }
    closeModal();
  }

  function onDeleted(event: CustomEvent<{ serviceBodyId: number }>) {
    serviceBodies = serviceBodies.filter((u) => u.id !== event.detail.serviceBodyId);
    showDeleteModal = false;
  }

  function openModal() {
    showModal = true;
  }

  function closeModal() {
    showModal = false;
  }

  onMount(getServiceBodies);

  $: {
    filteredServiceBodies = serviceBodies.sort((u1, u2) => u1.name.localeCompare(u2.name)).filter((u) => u.name.toLowerCase().indexOf(searchTerm.toLowerCase()) !== -1);
  }
</script>

<Nav />

<div class="mx-auto max-w-3xl p-2">
  <h2 class="mb-4 text-center text-xl font-semibold dark:text-white">{$translations.serviceBodiesTitle}</h2>
  {#if isLoaded}
    {#if serviceBodies.length > 1}
      <TableSearch placeholder={$translations.searchByName} hoverable={true} bind:inputValue={searchTerm}>
        <TableHead>
          <TableHeadCell colspan={$authenticatedUser?.type === 'admin' ? '2' : '1'}>
            {#if $authenticatedUser?.type === 'admin'}
              <div class="flex">
                <div class="mt-2.5 grow">Name</div>
                <div><Button on:click={() => handleAdd()} class="whitespace-nowrap" aria-label={$translations.addServiceBody}>{$translations.addServiceBody}</Button></div>
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
                    <TrashBinOutline title={{ id: 'deleteServiceBody', title: $translations.deleteServiceBody }} ariaLabel={$translations.deleteServiceBody} />
                  </Button>
                </TableBodyCell>
              {/if}
            </TableBodyRow>
          {/each}
        </TableBody>
      </TableSearch>
    {:else if $authenticatedUser?.type === 'admin'}
      <div class="p-2">
        <ServiceBodyForm {serviceBodies} {selectedServiceBody} on:saved={onSaved} />
      </div>
    {:else}
      <P class="text-center">{$translations.noServiceBodiesTitle}</P>
    {/if}
  {/if}
</div>

<ServiceBodyModal bind:showModal {serviceBodies} {selectedServiceBody} on:saved={onSaved} on:close={closeModal} />
<ServiceBodyDeleteModal bind:showDeleteModal {deleteServiceBody} on:deleted={onDeleted} />
