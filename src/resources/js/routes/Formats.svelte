<script lang="ts">
  import { Button, TableBody, TableBodyCell, TableBodyRow, TableHead, TableHeadCell, TableSearch } from 'flowbite-svelte';
  import { TrashBinOutline } from 'flowbite-svelte-icons';
  import Nav from '../components/NavBar.svelte';
  import FormatModal from '../components/FormatModal.svelte';
  import FormatDeleteModal from '../components/FormatDeleteModal.svelte';

  import { authenticatedUser } from '../stores/apiCredentials';
  import { spinner } from '../stores/spinner';
  import { translations } from '../stores/localization';
  import RootServerApi from '../lib/RootServerApi';

  import { onMount } from 'svelte';
  import type { Format } from 'bmlt-root-server-client';
  import FormatForm from '../components/FormatForm.svelte';

  const reservedFormatKeys = ['HY', 'TC', 'VM'];

  let isLoaded = false;
  let formats: Format[] = [];
  let filteredFormats: Format[] = [];
  let showModal = false;
  let showDeleteModal = false;
  let searchTerm = '';
  let selectedFormat: Format | null;
  let deleteFormat: Format;

  const language = translations.getLanguage();

  async function getFormats(): Promise<void> {
    try {
      spinner.show();
      formats = await RootServerApi.getFormats();
      formats.sort((a, b) => getFormatName(a).localeCompare(getFormatName(b)));
      isLoaded = true;
    } catch (error: any) {
      await RootServerApi.handleErrors(error);
    } finally {
      spinner.hide();
    }
  }

  function handleAdd() {
    selectedFormat = null;
    openModal();
  }

  function handleEdit(format: Format) {
    selectedFormat = format;
    openModal();
  }

  function handleDelete(event: MouseEvent, format: Format) {
    event.stopPropagation();
    deleteFormat = format;
    showDeleteModal = true;
  }

  function onSaved(event: CustomEvent<{ format: Format }>) {
    const format = event.detail.format;
    const i = formats.findIndex((f) => f.id === format.id);
    if (i === -1) {
      formats = [...formats, format].sort((a, b) => getFormatName(a).localeCompare(getFormatName(b)));
    } else {
      formats[i] = format;
    }
    closeModal();
  }

  function onDeleted(event: CustomEvent<{ formatId: number }>) {
    formats = formats.filter((f) => f.id !== event.detail.formatId);
    showDeleteModal = false;
  }

  function openModal() {
    showModal = true;
  }

  function closeModal() {
    showModal = false;
  }

  function isReserved(f: Format): boolean {
    return f.translations.find((t) => t.language === 'en' && reservedFormatKeys.includes(t.key)) !== undefined;
  }

  // Get the name of the format in the current language.  If no translation for that language is available, return the name in
  // English; if no English version, then just pick the first one in the array.  If that doesn't exist either then return a blank.
  // This last case only arises when trying to create a format with no translations; the UI signals an error if that happens.
  export function getFormatName(format: Format): string {
    const n = format.translations.find((t) => t.language === language);
    if (n) {
      return n.name;
    } else {
      const e = format.translations.find((t) => t.language === 'en');
      if (e) {
        return e.name + ' (' + $translations.noTranslationAvailable + ')';
      } else if (format.translations[0]) {
        return format.translations[0].name + ' (' + $translations.noTranslationAvailable + ')';
      } else {
        return '';
      }
    }
  }

  onMount(getFormats);

  $: {
    // prettier-ignore
    filteredFormats = formats
      .sort((f1, f2) => getFormatName(f1).localeCompare(getFormatName(f2)))
      .filter((f) => getFormatName(f).toLowerCase().indexOf(searchTerm.toLowerCase()) !== -1);
  }

  // In the HTML below, we can assume that the authenticatedUser is the admin -- if not, just show a blank page.
  // Formats won't appear in the nav bar, but somebody could get to this page directly.  (There isn't any private
  // information on the formats page, and the server wouldn't let them save, so this wouldn't be a big deal however.)
</script>

<Nav />

<div class="mx-auto max-w-3xl p-2">
  <h2 class="mb-4 text-center text-xl font-semibold dark:text-white">{$translations.formatsTitle}</h2>
  {#if isLoaded && $authenticatedUser?.type === 'admin'}
    {#if formats.length > 0}
      <TableSearch placeholder={$translations.searchByName} hoverable={true} bind:inputValue={searchTerm}>
        <TableHead>
          <TableHeadCell colspan={2}>
            <div class="flex">
              <div class="mt-2.5 grow">{$translations.nameTitle}</div>
              <div><Button on:click={() => handleAdd()} class="whitespace-nowrap" aria-label={$translations.addFormat}>{$translations.addFormat}</Button></div>
            </div>
          </TableHeadCell>
        </TableHead>
        <TableBody>
          {#each filteredFormats as format}
            <TableBodyRow on:click={() => handleEdit(format)} class="cursor-pointer" aria-label={$translations.editFormat}>
              <TableBodyCell class="whitespace-normal">{getFormatName(format)}</TableBodyCell>
              {#if $authenticatedUser?.type === 'admin'}
                <TableBodyCell class="text-right">
                  <Button
                    color="none"
                    on:click={(e) => handleDelete(e, format)}
                    class="text-blue-700 dark:text-blue-500"
                    disabled={isReserved(format)}
                    aria-label={$translations.deleteFormat + ' ' + getFormatName(format)}
                  >
                    <TrashBinOutline title={{ id: 'deleteFormat', title: $translations.deleteFormat }} ariaLabel={$translations.deleteFormat} />
                  </Button>
                </TableBodyCell>
              {/if}
            </TableBodyRow>
          {/each}
        </TableBody>
      </TableSearch>
    {:else}
      <div class="p-2">
        <FormatForm {selectedFormat} {reservedFormatKeys} on:saved={onSaved} />
      </div>
    {/if}
  {/if}
</div>

<FormatModal bind:showModal {selectedFormat} {reservedFormatKeys} on:saved={onSaved} on:close={closeModal} />
<FormatDeleteModal bind:showDeleteModal {deleteFormat} formatName={deleteFormat ? getFormatName(deleteFormat) : ''} on:deleted={onDeleted} />
