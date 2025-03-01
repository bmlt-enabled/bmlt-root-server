<script lang="ts">
  import { onMount } from 'svelte';
  import type { SvelteComponent } from 'svelte';
  import { Button, ButtonGroup, Checkbox, Dropdown, Indicator, Label, Select, TableBody, TableBodyCell, TableBodyRow, TableHead, TableHeadCell, TableSearch } from 'flowbite-svelte';
  import { ChevronDownOutline, ChevronLeftOutline, ChevronRightOutline, ChevronUpOutline, FilterSolid, PlusOutline } from 'flowbite-svelte-icons';

  import { convertTo12Hour, is24hrTime, isCommaSeparatedNumbers } from '../lib/utils';
  import { translations } from '../stores/localization';
  import { authenticatedUser } from '../stores/apiCredentials';
  import type { Meeting, ServiceBody, Format } from 'bmlt-root-server-client';
  import MeetingEditModal from './MeetingEditModal.svelte';
  import { spinner } from '../stores/spinner';
  import RootServerApi from '../lib/RootServerApi';
  import ServiceBodiesTree from './ServiceBodiesTree.svelte';

  export let formats: Format[];
  export let serviceBodies: ServiceBody[];

  let meetings: Meeting[] = [];
  let meetingIds: string = '';
  let selectedServiceBodies: string[] = serviceBodies.map((serviceBody) => serviceBody.id.toString());
  let divClass = 'bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-visible pt-3';
  let innerDivClass = 'flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4';
  let searchClass = 'w-full md:w-1/2 relative';
  let inputClass = 'text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2 pl-10 dark:bg-gray-700 dark:text-white';
  let searchTerm: string = '';
  let currentPosition: number = 0;
  let itemsPerPage: number = 20;
  const itemsPerPageItems = [10, 20, 40, 60, 80, 100].map((value) => ({ value, name: value.toString() }));
  const showPage: number = 5;
  let totalPages: number = 0;
  let pagesToShow: number[] = [];
  let selectedTimes: string[] = [];
  let selectedPublished: string[] = [];
  let selectedMeeting: Meeting | null;
  let showModal = false;
  let tableSearchRef: SvelteComponent | null = null;
  let sortColumn: string | null = null;
  let sortDirection: 'asc' | 'desc' = 'asc';
  const weekdayChoices = ($translations.daysOfWeek as string[]).map((day: string, index: number) => ({
    value: index.toString(),
    label: day
  }));

  let selectedDays: string[] = weekdayChoices.map((day) => day.value);
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
    getMeetings(searchTerm, selectedDays.join(','), selectedServiceBodies.join(','), meetingIds);
  }

  $: filteredItems = meetings
    .filter((meeting) => {
      const matchesDay = selectedDays.length > 0 ? selectedDays.includes(meeting.day.toString()) : true;
      const matchesPublished = selectedPublished.length > 0 ? selectedPublished.includes(String(meeting.published)) : true;
      const matchesSearch =
        // search by name, id or location
        meeting.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        String(meeting.id).includes(searchTerm) ||
        [meeting.locationStreet, meeting.locationCitySubsection, meeting.locationMunicipality, meeting.locationProvince, meeting.locationSubProvince, meeting.locationPostalCode1]
          .filter(Boolean)
          .join(', ')
          .toLowerCase()
          .includes(searchTerm.toLowerCase());
      const matchesTime =
        selectedTimes.length === 0 ||
        selectedTimes.some((time) => {
          if (time === 'morning') {
            return meeting.startTime >= '00:00' && meeting.startTime < '12:00';
          } else if (time === 'afternoon') {
            return meeting.startTime >= '12:00' && meeting.startTime < '18:00';
          } else if (time === 'evening') {
            return meeting.startTime >= '18:00' && meeting.startTime <= '23:59';
          }
          return false;
        });
      return matchesTime && matchesPublished && matchesDay && matchesSearch;
    })
    .sort((a, b) => {
      // Sort by day then time
      const dayComparison = a.day - b.day;
      if (dayComparison !== 0) return dayComparison;
      return a.startTime.localeCompare(b.startTime);
    });

  $: {
    currentPosition = 0;
    renderPagination(filteredItems.length);
  }

  let startPage: number;
  let endPage: number;
  let startRange: number;
  let endRange: number;

  const renderPagination = (totalItems: number) => {
    totalPages = Math.ceil(totalItems / itemsPerPage);
    const currentPage = Math.ceil((currentPosition + 1) / itemsPerPage);

    startPage = currentPage - Math.floor(showPage / 2);
    startPage = Math.max(1, startPage);
    endPage = Math.min(startPage + showPage - 1, totalPages);

    pagesToShow = Array.from({ length: endPage - startPage + 1 }, (_, i) => startPage + i);

    startRange = currentPosition + 1;
    endRange = Math.min(startRange + itemsPerPage - 1, totalItems);
  };

  const loadNextPage = () => {
    if (currentPosition + itemsPerPage < filteredItems.length) {
      currentPosition += itemsPerPage;
      updateDataAndPagination();
    }
  };

  const loadPreviousPage = () => {
    if (currentPosition - itemsPerPage >= 0) {
      currentPosition -= itemsPerPage;
      updateDataAndPagination();
    }
  };

  const updateDataAndPagination = () => {
    renderPagination(filteredItems.length);
  };

  const updateItemsPerPage = () => {
    currentPosition = 0; // Reset to first page when itemsPerPage changes
    renderPagination(filteredItems.length);
  };

  function handleSort(column: keyof Meeting) {
    if (sortColumn === column) {
      sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
      sortColumn = column;
      sortDirection = 'asc';
    }
    filteredItems = [...filteredItems].sort((a, b) => {
      const valA = a[column];
      const valB = b[column];

      if (valA === undefined && valB === undefined) return 0;
      if (valA === undefined) return sortDirection === 'asc' ? 1 : -1;
      if (valB === undefined) return sortDirection === 'asc' ? -1 : 1;

      if (valA < valB) return sortDirection === 'asc' ? -1 : 1;
      if (valA > valB) return sortDirection === 'asc' ? 1 : -1;
      return 0;
    });
    currentPosition = 0;
    renderPagination(filteredItems.length);
    currentPageItems = filteredItems.slice(currentPosition, currentPosition + itemsPerPage);
  }

  const goToPage = (pageNumber: number) => {
    currentPosition = (pageNumber - 1) * itemsPerPage;
    currentPageItems = filteredItems.slice(currentPosition, currentPosition + itemsPerPage);
    renderPagination(filteredItems.length);
  };

  function onSaved(event: CustomEvent<{ meeting: Meeting }>) {
    const meeting = event.detail.meeting;
    const i = meetings.findIndex((m) => m.id === meeting.id);
    if (i === -1) {
      meetings = [...meetings, meeting];
    } else {
      meetings[i] = meeting;
    }
    closeModal();
  }

  function handleAdd() {
    selectedMeeting = null;
    openModal();
  }

  function handleEdit(meeting: Meeting) {
    selectedMeeting = meeting;
    openModal();
  }

  function onDeleted(event: CustomEvent<{ meetingId: number }>) {
    meetings = meetings.filter((m) => m.id !== event.detail.meetingId);
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
  }

  function handleEnterKey(event: KeyboardEvent) {
    if (event.key === 'Enter') {
      searchMeetings();
    }
  }

  onMount(() => {
    renderPagination(filteredItems.length);
    const searchInputElement = tableSearchRef?.shadowRoot?.getElementById('table-search') || (document.getElementById('table-search') as HTMLInputElement | null);
    if (searchInputElement) {
      searchInputElement.addEventListener('keydown', handleEnterKey);
    }
    if (serviceBodies.length === 1) {
      searchMeetings();
    }
    return () => {
      if (searchInputElement) {
        searchInputElement.removeEventListener('keydown', handleEnterKey);
      }
    };
  });

  $: currentPageItems = filteredItems.slice(currentPosition, currentPosition + itemsPerPage);

  let isAllDaysSelected = selectedDays.length === weekdayChoices.length;
</script>

<TableSearch placeholder={$translations.filter} bind:this={tableSearchRef} hoverable={true} bind:inputValue={searchTerm} {divClass} {innerDivClass} {searchClass} {inputClass}>
  <div slot="header" class="flex w-full flex-shrink-0 flex-col items-stretch justify-end space-y-2 md:w-auto md:flex-row md:items-center md:space-x-3 md:space-y-0">
    {#if serviceBodies.length > 1}
      <Button color="alternative" class="relative" aria-label={$translations.serviceBodiesTitle}>
        {$translations.serviceBodiesTitle}
        {#if selectedServiceBodies.length > 0}
          <Indicator color="red" size="sm" placement="top-right" />
        {/if}
      </Button>
      <Dropdown class="w-90 top-full z-50 space-y-2 p-3 text-sm" open={true}>
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
    <Dropdown class="top-full z-50 w-48 space-y-2 p-3 text-sm">
      <h6 class="text-sm font-medium text-gray-900 dark:text-white">{$translations.searchByDay}</h6>
      <Button on:click={toggleAllDays} size="xs" color="primary" class="w-full">
        {isAllDaysSelected ? $translations.unselectAllDays : $translations.selectAllDays}
      </Button>
      <Checkbox name="weekdays" choices={weekdayChoices} bind:group={selectedDays} groupLabelClass="justify-between" />
    </Dropdown>
    <Button color="alternative" class="relative">
      {$translations.published}
      {#if selectedPublished.length > 0}
        <Indicator color="red" size="sm" placement="top-right" />
      {/if}
      <FilterSolid class="ml-2 h-3 w-3 " />
    </Button>
    <Dropdown class="w-48 space-y-2 p-3 text-sm">
      <Checkbox name="times" choices={publishedChoices} bind:group={selectedPublished} groupInputClass="ms-2" groupLabelClass="" />
    </Dropdown>
    <Button color="alternative" class="relative">
      {$translations.time}
      {#if selectedTimes.length > 0}
        <Indicator color="red" size="sm" placement="top-right" />
      {/if}
      <FilterSolid class="ml-2 h-3 w-3 " />
    </Button>
    <Dropdown class="w-48 space-y-2 p-3 text-sm">
      <h6 class="mb-3 text-sm font-medium text-gray-900 dark:text-white">{$translations.chooseStartTime}</h6>
      <Checkbox name="times" choices={timeChoices} bind:group={selectedTimes} groupInputClass="ms-2" groupLabelClass="" />
    </Dropdown>
    <Button on:click={searchMeetings}>{$translations.search}</Button>
    {#if $authenticatedUser?.type !== 'observer'}
      <Button on:click={() => handleAdd()} aria-label={$translations.addMeeting}><PlusOutline class="mr-2 h-3.5 w-3.5" />{$translations.addMeeting}</Button>
    {/if}
  </div>
  <TableHead>
    {#if meetings.length}
      <TableHeadCell padding="px-4 py-3 whitespace-nowrap" scope="col" on:click={() => handleSort('day')}>
        Day
        {#if sortColumn === 'day'}
          {#if sortDirection === 'asc'}
            <ChevronUpOutline class="ml-1 inline-block h-3 w-3" />
          {:else}
            <ChevronDownOutline class="ml-1 inline-block h-3 w-3" />
          {/if}
        {/if}
      </TableHeadCell>
      <TableHeadCell padding="px-4 py-3 whitespace-nowrap" scope="col" on:click={() => handleSort('startTime')}>
        Time
        {#if sortColumn === 'startTime'}
          {#if sortDirection === 'asc'}
            <ChevronUpOutline class="ml-1 inline-block h-3 w-3" />
          {:else}
            <ChevronDownOutline class="ml-1 inline-block h-3 w-3" />
          {/if}
        {/if}
      </TableHeadCell>
      <TableHeadCell padding="px-4 py-3" scope="col" on:click={() => handleSort('name')}>
        Meeting
        {#if sortColumn === 'name'}
          {#if sortDirection === 'asc'}
            <ChevronUpOutline class="ml-1 inline-block h-3 w-3" />
          {:else}
            <ChevronDownOutline class="ml-1 inline-block h-3 w-3" />
          {/if}
        {/if}
      </TableHeadCell>
      <TableHeadCell padding="px-4 py-3" scope="col" on:click={() => handleSort('locationStreet')}>
        Location
        {#if sortColumn === 'location'}
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
      <TableBodyRow on:click={() => handleEdit(meeting)}>
        <TableBodyCell tdClass={meeting.published ? 'px-4 py-3 whitespace-nowrap' : 'bg-yellow-400 px-4 py-3 whitespace-nowrap min-w-[100px]'}>{$translations.daysOfWeek[meeting.day]}</TableBodyCell>
        <TableBodyCell tdClass={meeting.published ? 'px-4 py-3 whitespace-nowrap' : 'bg-yellow-400 px-4 py-3 whitespace-nowrap min-w-[100px]'}
          >{is24hrTime() ? meeting.startTime : convertTo12Hour(meeting.startTime)}</TableBodyCell
        >
        <TableBodyCell tdClass={meeting.published ? 'px-4 py-3' : 'bg-yellow-400 px-4 py-3'}>{meeting.name}</TableBodyCell>
        <TableBodyCell tdClass={meeting.published ? 'px-4 py-3' : 'bg-yellow-400 px-4 py-3 text-wrap'}>
          {[meeting.locationStreet, meeting.locationCitySubsection, meeting.locationMunicipality, meeting.locationProvince, meeting.locationSubProvince, meeting.locationPostalCode1]
            .filter(Boolean)
            .join(', ')}
        </TableBodyCell>
      </TableBodyRow>
    {/each}
  </TableBody>
  <div slot="footer" class="flex flex-col items-start justify-between space-y-3 p-4 md:flex-row md:items-center md:space-y-0 {meetings.length ? '' : 'hidden'}" aria-label="Table navigation">
    {#if meetings.length}
      <span class="flex items-center space-x-1 text-sm font-normal text-gray-500 dark:text-gray-400">
        <span>{$translations.paginationShowing}</span>
        <span class="font-semibold text-gray-900 dark:text-white">{startRange}-{endRange}</span>
        <span>{$translations.paginationOf}</span>
        <span class="font-semibold text-gray-900 dark:text-white">{filteredItems.length}</span>
        <span class="mx-2 text-gray-500 dark:text-gray-400">/</span>
        <span class="ml-4 flex items-center space-x-1">
          <Label for="itemsPerPage" class="text-sm font-medium text-gray-700 dark:text-gray-300">{$translations.meetingsPerPage}</Label>
          <Select id="itemsPerPage" items={itemsPerPageItems} bind:value={itemsPerPage} name="itemsPerPage" class="w-20 dark:bg-gray-600" on:change={updateItemsPerPage} />
        </span>
      </span>
      <ButtonGroup>
        <Button on:click={loadPreviousPage} disabled={currentPosition === 0}>
          <ChevronLeftOutline size="xs" class="m-1.5" />
        </Button>
        {#each pagesToShow as pageNumber}
          <Button on:click={() => goToPage(pageNumber)}>{pageNumber}</Button>
        {/each}
        <Button on:click={loadNextPage} disabled={currentPosition + itemsPerPage >= filteredItems.length}>
          <ChevronRightOutline size="xs" class="m-1.5" />
        </Button>
      </ButtonGroup>
    {/if}
  </div>
</TableSearch>

<MeetingEditModal bind:showModal {selectedMeeting} {serviceBodies} {formats} on:saved={onSaved} on:close={closeModal} on:deleted={onDeleted} />
