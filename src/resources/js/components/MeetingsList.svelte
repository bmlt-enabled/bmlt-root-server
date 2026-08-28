<script lang="ts">
  import { onMount, onDestroy } from 'svelte';
  import type { SvelteComponent } from 'svelte';
  import { Button, ButtonGroup, Checkbox, Dropdown, Indicator, Label, Select, TableBody, TableBodyCell, TableBodyRow, TableHead, TableHeadCell, TableSearch } from 'flowbite-svelte';
  import { ChevronDownOutline, ChevronLeftOutline, ChevronRightOutline, ChevronUpOutline, FilterSolid, PlusOutline } from 'flowbite-svelte-icons';

  import { convertTo12Hour, is24hrTime, isCommaSeparatedNumbers } from '../lib/utils';
  import { translations } from '../stores/localization';
  import { authenticatedUser } from '../stores/apiCredentials';
  import type { Meeting, ServiceBody, Format } from 'bmlt-server-client';
  import MeetingEditModal from './MeetingEditModal.svelte';
  import { spinner } from '../stores/spinner';
  import RootServerApi from '../lib/ServerApi';
  import ServiceBodiesTree from './ServiceBodiesTree.svelte';
  import { meetingsState } from '../stores/meetingsState';

  interface Props {
    serviceBodies: ServiceBody[];
    formats: Format[];
  }

  let { serviceBodies, formats }: Props = $props();

  let meetings: Meeting[] = $state([]);
  let meetingIds: string = '';
  let selectedServiceBodies: string[] = $state(serviceBodies.map((serviceBody) => serviceBody.id.toString()));
  let tableSearchClasses = {
    root: 'bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-visible pt-3',
    inner: 'flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4',
    search: 'w-full md:w-1/2 relative',
    input: 'text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2 pl-10 dark:bg-gray-700 dark:text-white'
  };
  let searchTerm: string = $state('');
  let currentPosition: number = $state(0);
  let itemsPerPage: number = $state(20);
  const itemsPerPageItems = [10, 20, 40, 60, 80, 100].map((value) => ({ value, name: value.toString() }));
  const showPage: number = 5;
  let dropdownOpen = $state(false);
  let selectedTimes: string[] = $state([]);
  let selectedPublished: string[] = $state([]);
  let selectedMeeting: Meeting | null = $state(null);
  let showModal = $state(false);
  let tableSearchRef: SvelteComponent | null = null;
  let sortColumn: keyof Meeting | null = $state(null);
  let sortDirection: 'asc' | 'desc' = $state('asc');
  let lastEditedMeetingId: number | null = $state(null);
  const daysOfWeek: string[] = [$translations.day0, $translations.day1, $translations.day2, $translations.day3, $translations.day4, $translations.day5, $translations.day6];
  const weekdayChoices = daysOfWeek.map((day: string, index: number) => ({
    value: index.toString(),
    label: day
  }));

  let selectedDays: string[] = $state(weekdayChoices.map((day) => day.value));
  const timeChoices = [
    { value: 'morning', label: $translations.timeMorning },
    { value: 'afternoon', label: $translations.timeAfternoon },
    { value: 'evening', label: $translations.timeEvening }
  ];
  const publishedChoices = [
    { value: 'true', label: $translations.published },
    { value: 'false', label: $translations.unpublished }
  ];

  async function getMeetings(searchString: string = '', days: string = '', serviceBodyIds: string = '', meetingIds: string = ''): Promise<void> {
    try {
      spinner.show();
      meetings = await RootServerApi.getMeetings({
        searchString,
        days,
        serviceBodyIds,
        meetingIds
      });
    } catch (error: any) {
      await RootServerApi.handleErrors(error);
    } finally {
      spinner.hide();
    }
  }

  function searchMeetings() {
    if (isCommaSeparatedNumbers(searchTerm)) {
      meetingIds = searchTerm;
      searchTerm = '';
    } else {
      meetingIds = '';
    }
    lastEditedMeetingId = null;
    getMeetings(searchTerm, selectedDays.join(','), selectedServiceBodies.join(','), meetingIds);
  }

  const filteredItems = $derived(
    meetings
      .filter((meeting) => {
        const matchesDay = selectedDays.length > 0 ? selectedDays.includes(meeting.day.toString()) : true;
        const matchesPublished = selectedPublished.length > 0 ? selectedPublished.includes(String(meeting.published)) : true;
        const matchesSearch =
          (meeting.name?.toLowerCase() || '').includes(searchTerm.toLowerCase()) ||
          String(meeting.id).includes(searchTerm) ||
          [meeting.locationStreet, meeting.locationCitySubsection, meeting.locationMunicipality, meeting.locationProvince, meeting.locationSubProvince, meeting.locationPostalCode1]
            .filter(Boolean)
            .join(', ')
            .toLowerCase()
            .includes(searchTerm.toLowerCase());
        const matchesTime =
          selectedTimes.length === 0 ||
          selectedTimes.some((time) => {
            const startTime = meeting.startTime || '';
            if (time === 'morning') {
              return startTime >= '00:00' && startTime < '12:00';
            } else if (time === 'afternoon') {
              return startTime >= '12:00' && startTime < '18:00';
            } else if (time === 'evening') {
              return startTime >= '18:00' && startTime <= '23:59';
            }
            return false;
          });
        return matchesTime && matchesPublished && matchesDay && matchesSearch;
      })
      .sort((a, b) => {
        // Apply custom sorting if a sort column is selected
        if (sortColumn && sortColumn in a) {
          const valA = a[sortColumn];
          const valB = b[sortColumn];

          if (valA === undefined && valB === undefined) return 0;
          if (valA === undefined) return sortDirection === 'asc' ? 1 : -1;
          if (valB === undefined) return sortDirection === 'asc' ? -1 : 1;

          // Handle different data types properly
          if (typeof valA === 'string' && typeof valB === 'string') {
            return sortDirection === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
          }

          if (typeof valA === 'number' && typeof valB === 'number') {
            return sortDirection === 'asc' ? valA - valB : valB - valA;
          }

          // Fallback for mixed types
          const strA = String(valA);
          const strB = String(valB);
          return sortDirection === 'asc' ? strA.localeCompare(strB) : strB.localeCompare(strA);
        }

        // Default sort by day then time (your original sorting)
        const dayComparison = a.day - b.day;
        if (dayComparison !== 0) return dayComparison;
        return (a.startTime || '').localeCompare(b.startTime || '');
      })
  );

  const totalPages = $derived(Math.ceil(filteredItems.length / itemsPerPage));
  const currentPage = $derived(Math.ceil((currentPosition + 1) / itemsPerPage));
  const startPage = $derived(Math.max(1, currentPage - Math.floor(showPage / 2)));
  const endPage = $derived(Math.min(startPage + showPage - 1, totalPages));
  const startRange = $derived(currentPosition + 1);
  const endRange = $derived(Math.min(startRange + itemsPerPage - 1, filteredItems.length));
  const pagesToShow = $derived(Array.from({ length: endPage - startPage + 1 }, (_, i) => startPage + i));

  function loadNextPage() {
    if (currentPosition + itemsPerPage < filteredItems.length) {
      currentPosition += itemsPerPage;
      lastEditedMeetingId = null;
    }
  }

  function loadPreviousPage() {
    if (currentPosition - itemsPerPage >= 0) {
      currentPosition -= itemsPerPage;
      lastEditedMeetingId = null;
    }
  }

  function handleItemsPerPageChange() {
    currentPosition = 0; // Reset to first page when itemsPerPage changes
    lastEditedMeetingId = null;
  }

  function handleSort(column: keyof Meeting) {
    if (sortColumn === column) {
      sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
      sortColumn = column;
      sortDirection = 'asc';
    }

    currentPosition = 0;
  }

  function goToPage(pageNumber: number) {
    currentPosition = (pageNumber - 1) * itemsPerPage;
    lastEditedMeetingId = null;
  }

  function onSaved(meeting: Meeting) {
    const i = meetings.findIndex((m) => m.id === meeting.id);
    if (i === -1) {
      meetings = [...meetings, meeting];
    } else {
      meetings[i] = meeting;
    }
    lastEditedMeetingId = meeting.id;
    closeModal();
  }

  function handleAdd() {
    selectedMeeting = null;
    openModal();
  }

  function handleEdit(meeting: Meeting) {
    selectedMeeting = meeting;
    lastEditedMeetingId = meeting.id;
    openModal();
  }

  function onDeleted(meeting: Meeting) {
    meetings = meetings.filter((m) => m.id !== meeting.id);
    // Clamp the current page if deleting emptied the page we were on
    if (currentPosition > 0 && currentPosition >= filteredItems.length) {
      currentPosition = Math.max(0, (totalPages - 1) * itemsPerPage);
    }
    closeModal();
  }

  function openModal() {
    showModal = true;
  }

  function closeModal() {
    showModal = false;
  }

  function toggleAllDays() {
    if (selectedDays.length === weekdayChoices.length) {
      selectedDays = [];
    } else {
      selectedDays = weekdayChoices.map((day) => day.value);
    }
    currentPosition = 0;
    lastEditedMeetingId = null;
  }

  function handleEnterKey(event: KeyboardEvent) {
    if (event.key === 'Enter') {
      searchMeetings();
    }
  }

  function handleSearchTermChange(value: string) {
    searchTerm = value;
    currentPosition = 0;
    lastEditedMeetingId = null;
  }

  onMount(() => {
    // Restore state from store if it exists
    const storedState = $meetingsState;
    if (storedState.meetings.length > 0) {
      meetings = storedState.meetings;
      searchTerm = storedState.searchTerm;
      selectedServiceBodies = storedState.selectedServiceBodies.length > 0 ? storedState.selectedServiceBodies : serviceBodies.map((sb) => sb.id.toString());
      selectedDays = storedState.selectedDays.length > 0 ? storedState.selectedDays : weekdayChoices.map((day) => day.value);
      selectedTimes = storedState.selectedTimes;
      selectedPublished = storedState.selectedPublished;
      currentPosition = storedState.currentPosition;
      itemsPerPage = storedState.itemsPerPage;
      sortColumn = storedState.sortColumn;
      sortDirection = storedState.sortDirection;
      lastEditedMeetingId = storedState.lastEditedMeetingId;
      meetingIds = storedState.meetingIds;
    } else if (serviceBodies.length === 1) {
      // Initialize selectedServiceBodies for single service body case
      selectedServiceBodies = serviceBodies.map((sb) => sb.id.toString());
      searchMeetings();
    }

    const searchInputElement = tableSearchRef?.shadowRoot?.getElementById('table-search') || (document.getElementById('table-search') as HTMLInputElement | null);
    if (searchInputElement) {
      searchInputElement.addEventListener('keydown', handleEnterKey);
    }

    return () => {
      if (searchInputElement) {
        searchInputElement.removeEventListener('keydown', handleEnterKey);
      }
    };
  });

  onDestroy(() => {
    // Save current state to store when component unmounts
    meetingsState.set({
      meetings,
      searchTerm,
      selectedServiceBodies,
      selectedDays,
      selectedTimes,
      selectedPublished,
      currentPosition,
      itemsPerPage,
      sortColumn,
      sortDirection,
      lastEditedMeetingId,
      meetingIds
    });
  });

  const currentPageItems = $derived(filteredItems.slice(currentPosition, currentPosition + itemsPerPage));

  const isAllDaysSelected = $derived(selectedDays.length === weekdayChoices.length);
</script>

<TableSearch placeholder={$translations.filter} bind:this={tableSearchRef} hoverable={true} bind:inputValue={() => searchTerm, handleSearchTermChange} classes={tableSearchClasses}>
  {#snippet header()}
    <div class="flex w-full flex-shrink-0 flex-col items-stretch justify-end space-y-2 md:w-auto md:flex-row md:items-center md:space-y-0 md:space-x-3">
      {#if serviceBodies.length > 1}
        <Button color="alternative" class="relative" aria-label={$translations.serviceBodiesTitle}>
          {$translations.serviceBodiesTitle}
          {#if selectedServiceBodies.length > 0}
            <Indicator color="red" size="sm" placement="top-right" />
          {/if}
        </Button>
        <Dropdown class="top-full z-50 w-90 space-y-2 divide-y-0 p-3 text-sm" bind:isOpen={dropdownOpen}>
          <h6 class="mb-3 text-sm font-medium text-gray-900 dark:text-white">{$translations.searchByServiceBody}</h6>
          <ServiceBodiesTree {serviceBodies} bind:selectedValues={selectedServiceBodies} />
        </Dropdown>
      {/if}
      <Button color="alternative" class="relative" aria-label={$translations.day}>
        {$translations.day}
        {#if selectedDays.length > 0}
          <Indicator color="red" size="sm" placement="top-right" />
        {/if}
      </Button>
      <Dropdown class="top-full z-50 w-48 space-y-2 divide-y-0 p-3 text-sm">
        <h6 class="text-sm font-medium text-gray-900 dark:text-white">{$translations.searchByDay}</h6>
        <Button onclick={toggleAllDays} size="xs" color="primary" class="w-full">
          {isAllDaysSelected ? $translations.unselectAllDays : $translations.selectAllDays}
        </Button>
        <Checkbox
          name="weekdays"
          choices={weekdayChoices}
          bind:group={selectedDays}
          onchange={() => {
            currentPosition = 0;
            lastEditedMeetingId = null;
          }}
          class="justify-between"
        />
      </Dropdown>
      <Button color="alternative" class="relative">
        {$translations.published}
        {#if selectedPublished.length > 0}
          <Indicator color="red" size="sm" placement="top-right" />
        {/if}
        <FilterSolid class="ml-2 h-3 w-3 " />
      </Button>
      <Dropdown class="w-48 space-y-2 divide-y-0 p-3 text-sm">
        <Checkbox
          name="times"
          choices={publishedChoices}
          bind:group={selectedPublished}
          onchange={() => {
            currentPosition = 0;
            lastEditedMeetingId = null;
          }}
          class="ms-2"
        />
      </Dropdown>
      <Button color="alternative" class="relative">
        {$translations.time}
        {#if selectedTimes.length > 0}
          <Indicator color="red" size="sm" placement="top-right" />
        {/if}
        <FilterSolid class="ml-2 h-3 w-3 " />
      </Button>
      <Dropdown class="w-48 space-y-2 divide-y-0 p-3 text-sm">
        <h6 class="mb-3 text-sm font-medium text-gray-900 dark:text-white">{$translations.chooseStartTime}</h6>
        <Checkbox
          name="times"
          choices={timeChoices}
          bind:group={selectedTimes}
          onchange={() => {
            currentPosition = 0;
            lastEditedMeetingId = null;
          }}
          class="ms-2"
        />
      </Dropdown>
      <Button onclick={searchMeetings}>{$translations.search}</Button>
      {#if $authenticatedUser?.type === 'admin' || $authenticatedUser?.type === 'serviceBodyAdmin'}
        <Button onclick={() => handleAdd()} aria-label={$translations.addMeeting}>
          <PlusOutline class="mr-2 h-3.5 w-3.5" />{$translations.addMeeting}
        </Button>
      {/if}
    </div>
  {/snippet}

  <TableHead>
    {#if meetings.length}
      <TableHeadCell padding="px-4 py-3 whitespace-nowrap" scope="col" onclick={() => handleSort('day')}>
        {$translations.day}
        {#if sortColumn === 'day'}
          {#if sortDirection === 'asc'}
            <ChevronUpOutline class="ml-1 inline-block h-3 w-3" />
          {:else}
            <ChevronDownOutline class="ml-1 inline-block h-3 w-3" />
          {/if}
        {/if}
      </TableHeadCell>
      <TableHeadCell padding="px-4 py-3 whitespace-nowrap" scope="col" onclick={() => handleSort('startTime')}>
        {$translations.time}
        {#if sortColumn === 'startTime'}
          {#if sortDirection === 'asc'}
            <ChevronUpOutline class="ml-1 inline-block h-3 w-3" />
          {:else}
            <ChevronDownOutline class="ml-1 inline-block h-3 w-3" />
          {/if}
        {/if}
      </TableHeadCell>
      <TableHeadCell padding="px-4 py-3" scope="col" onclick={() => handleSort('name')}>
        {$translations.meeting}
        {#if sortColumn === 'name'}
          {#if sortDirection === 'asc'}
            <ChevronUpOutline class="ml-1 inline-block h-3 w-3" />
          {:else}
            <ChevronDownOutline class="ml-1 inline-block h-3 w-3" />
          {/if}
        {/if}
      </TableHeadCell>
      <TableHeadCell padding="px-4 py-3" scope="col" onclick={() => handleSort('locationStreet')}>
        {$translations.location}
        {#if sortColumn === 'locationStreet'}
          {#if sortDirection === 'asc'}
            <ChevronUpOutline class="ml-1 inline-block h-3 w-3" />
          {:else}
            <ChevronDownOutline class="ml-1 inline-block h-3 w-3" />
          {/if}
        {/if}
      </TableHeadCell>
    {:else}
      <TableHeadCell class="bg-white dark:bg-gray-800" scope="col"></TableHeadCell>
      <TableHeadCell class="bg-white dark:bg-gray-800" scope="col"></TableHeadCell>
      <TableHeadCell class="bg-white dark:bg-gray-800" scope="col"></TableHeadCell>
      <TableHeadCell class="bg-white dark:bg-gray-800" scope="col"></TableHeadCell>
    {/if}
  </TableHead>
  <TableBody>
    {#each currentPageItems as meeting (meeting.id)}
      <TableBodyRow onclick={() => handleEdit(meeting)} class={meeting.id === lastEditedMeetingId ? 'bg-blue-50 dark:bg-blue-900' : ''}>
        <TableBodyCell class={meeting.published ? 'px-4 py-3 whitespace-nowrap' : 'min-w-[100px] bg-yellow-200 px-4 py-3 whitespace-nowrap text-gray-800'}>
          {daysOfWeek[meeting.day]}
        </TableBodyCell>
        <TableBodyCell class={meeting.published ? 'px-4 py-3 whitespace-nowrap' : 'min-w-[100px] bg-yellow-200 px-4 py-3 whitespace-nowrap text-gray-800'}>
          {#if meeting.startTime}
            {is24hrTime() ? meeting.startTime : convertTo12Hour(meeting.startTime)}
          {/if}
        </TableBodyCell>
        <TableBodyCell class={meeting.published ? 'px-4 py-3' : 'bg-yellow-200 px-4 py-3 text-gray-800'}>
          {meeting.name || ''}
        </TableBodyCell>
        <TableBodyCell class={meeting.published ? 'px-4 py-3' : 'bg-yellow-200 px-4 py-3 text-wrap text-gray-800'}>
          {[meeting.locationStreet, meeting.locationCitySubsection, meeting.locationMunicipality, meeting.locationProvince, meeting.locationSubProvince, meeting.locationPostalCode1]
            .filter(Boolean)
            .join(', ')}
        </TableBodyCell>
      </TableBodyRow>
    {/each}
  </TableBody>

  {#snippet footer()}
    <div class="flex flex-col items-start justify-between space-y-3 p-4 md:flex-row md:items-center md:space-y-0 {meetings.length ? '' : 'hidden'}" aria-label="Table navigation">
      {#if meetings.length}
        <span class="flex items-center space-x-1 text-sm font-normal text-gray-500 dark:text-gray-400">
          <span>{$translations.paginationShowing}</span>
          <span class="font-semibold text-gray-900 dark:text-white">{startRange}-{endRange}</span>
          <span>{$translations.paginationOf}</span>
          <span class="font-semibold text-gray-900 dark:text-white">{filteredItems.length}</span>
          <span class="mx-2 text-gray-500 dark:text-gray-400">/</span>
          <span class="ml-4 flex items-center space-x-1">
            <Label for="itemsPerPage" class="text-sm font-medium text-gray-700 dark:text-gray-300">{$translations.meetingsPerPage}</Label>
            <Select
              id="itemsPerPage"
              items={itemsPerPageItems}
              bind:value={itemsPerPage}
              onchange={handleItemsPerPageChange}
              name="itemsPerPage"
              placeholder={$translations.chooseOption}
              class="w-20 rounded-lg dark:bg-gray-600"
            />
          </span>
        </span>
        <ButtonGroup>
          <Button onclick={loadPreviousPage} disabled={currentPosition === 0}>
            <ChevronLeftOutline size="xs" class="m-1.5" />
          </Button>
          {#each pagesToShow as pageNumber}
            <Button onclick={() => goToPage(pageNumber)} color={pageNumber === currentPage ? 'primary' : 'alternative'}>
              {pageNumber}
            </Button>
          {/each}
          <Button onclick={loadNextPage} disabled={currentPosition + itemsPerPage >= filteredItems.length}>
            <ChevronRightOutline size="xs" class="m-1.5" />
          </Button>
        </ButtonGroup>
      {/if}
    </div>
  {/snippet}
</TableSearch>

{#if showModal}
  <MeetingEditModal bind:showModal {selectedMeeting} {serviceBodies} {formats} {onSaved} onClosed={closeModal} {onDeleted} />
{/if}
