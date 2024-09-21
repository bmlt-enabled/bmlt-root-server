<script lang="ts">
  import { onMount } from 'svelte';
  import { TableBody, TableBodyCell, TableBodyRow, TableHead, TableHeadCell, TableSearch, Button, Dropdown, Checkbox, ButtonGroup } from 'flowbite-svelte';

  import { PlusOutline, FilterSolid, ChevronDownOutline, ChevronUpOutline, ChevronRightOutline, ChevronLeftOutline } from 'flowbite-svelte-icons';

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
  let selectedDays: string[] = [];
  let selectedServiceBodies: string[] = [];
  let divClass = 'bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-visible';
  let innerDivClass = 'flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4';
  let searchClass = 'w-full md:w-1/2 relative';
  let classInput = 'text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2  pl-10';
  let searchTerm: string = '';
  let currentPosition: number = 0;
  const itemsPerPage: number = 20;
  const showPage: number = 5;
  let totalPages: number = 0;
  let pagesToShow: number[] = [];
  let selectedTimes: string[] = [];
  let selectedPublished: string[] = [];
  let selectedMeeting: Meeting | null;
  let showModal = false;
  let sortColumn: string | null = null;
  let sortDirection: 'asc' | 'desc' = 'asc';
  const weekdayChoices = $translations.daysOfWeek.map((day, index) => ({
    value: index.toString(),
    label: day
  }));
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
    }
    getMeetings(searchTerm, selectedDays.join(','), selectedServiceBodies.join(','), meetingIds);
  }

  $: filteredItems = meetings
    .filter((meeting) => {
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
      return matchesTime && matchesPublished && matchesSearch;
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

  onMount(() => {
    renderPagination(filteredItems.length);
  });

  $: currentPageItems = filteredItems.slice(currentPosition, currentPosition + itemsPerPage);
</script>

<TableSearch placeholder={$translations.filter} hoverable={true} bind:inputValue={searchTerm} {divClass} {innerDivClass} {searchClass} {classInput}>
  <div slot="header" class="flex w-full flex-shrink-0 flex-col items-stretch justify-end space-y-2 md:w-auto md:flex-row md:items-center md:space-x-3 md:space-y-0">
    <Button color="alternative">{$translations.serviceBodiesTitle}</Button>
    <Dropdown class="w-90 top-full z-50 space-y-2 p-3 text-sm">
      <ServiceBodiesTree {serviceBodies} bind:selectedValues={selectedServiceBodies} />
    </Dropdown>
    <Button color="alternative">{$translations.day}</Button>
    <Dropdown class="top-full z-50 w-48 space-y-2 p-3 text-sm">
      <h6 class="mb-3 text-sm font-medium text-gray-900 dark:text-white">{$translations.chooseDay}</h6>
      <Checkbox name="weekdays" choices={weekdayChoices} bind:group={selectedDays} groupInputClass="ms-2" groupLabelClass="" />
    </Dropdown>
    {#if meetings.length}
      <Button color="alternative">{$translations.published}<FilterSolid class="ml-2 h-3 w-3 " /></Button>
      <Dropdown class="w-48 space-y-2 p-3 text-sm">
        <Checkbox name="times" choices={publishedChoices} bind:group={selectedPublished} groupInputClass="ms-2" groupLabelClass="" />
      </Dropdown>
      <Button color="alternative">{$translations.time}<FilterSolid class="ml-2 h-3 w-3 " /></Button>
      <Dropdown class="w-48 space-y-2 p-3 text-sm">
        <h6 class="mb-3 text-sm font-medium text-gray-900 dark:text-white">{$translations.chooseStartTime}</h6>
        <Checkbox name="times" choices={timeChoices} bind:group={selectedTimes} groupInputClass="ms-2" groupLabelClass="" />
      </Dropdown>
    {/if}
    <Button on:click={searchMeetings}>{$translations.search}</Button>
    {#if $authenticatedUser?.type !== 'observer' && meetings.length}
      <Button on:click={() => handleAdd()}><PlusOutline class="mr-2 h-3.5 w-3.5" />{$translations.addMeeting}</Button>
    {/if}
  </div>
  <TableHead>
    {#if meetings.length}
      <TableHeadCell padding="px-4 py-3" scope="col" on:click={() => handleSort('day')}>
        Day
        {#if sortColumn === 'day'}
          {#if sortDirection === 'asc'}
            <ChevronUpOutline class="ml-1 inline-block h-3 w-3" />
          {:else}
            <ChevronDownOutline class="ml-1 inline-block h-3 w-3" />
          {/if}
        {/if}
      </TableHeadCell>
      <TableHeadCell padding="px-4 py-3" scope="col" on:click={() => handleSort('startTime')}>
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
      <TableHeadCell style="background-color: rgb(31 41 55);" scope="col"></TableHeadCell>
      <TableHeadCell style="background-color: rgb(31 41 55);" scope="col"></TableHeadCell>
      <TableHeadCell style="background-color: rgb(31 41 55);" scope="col"></TableHeadCell>
      <TableHeadCell style="background-color: rgb(31 41 55);" scope="col"></TableHeadCell>
    {/if}
  </TableHead>
  <TableBody>
    {#each currentPageItems as meeting (meeting.id)}
      <TableBodyRow on:click={() => handleEdit(meeting)}>
        <TableBodyCell tdClass={meeting.published ? 'px-4 py-3' : 'bg-yellow-400 px-4 py-3'}>{$translations.daysOfWeek[meeting.day]}</TableBodyCell>
        <TableBodyCell tdClass={meeting.published ? 'px-4 py-3' : 'bg-yellow-400 px-4 py-3 text-nowrap'}>{is24hrTime() ? meeting.startTime : convertTo12Hour(meeting.startTime)}</TableBodyCell>
        <TableBodyCell tdClass={meeting.published ? 'px-4 py-3' : 'bg-yellow-400 px-4 py-3'}>{meeting.name}</TableBodyCell>
        <TableBodyCell tdClass={meeting.published ? 'px-4 py-3' : 'bg-yellow-400 px-4 py-3 text-wrap'}>
          {[meeting.locationStreet, meeting.locationCitySubsection, meeting.locationMunicipality, meeting.locationProvince, meeting.locationSubProvince, meeting.locationPostalCode1]
            .filter(Boolean)
            .join(', ')}
        </TableBodyCell>
      </TableBodyRow>
    {/each}
  </TableBody>
  <div slot="footer" class="flex flex-col items-start justify-between space-y-3 p-4 md:flex-row md:items-center md:space-y-0" aria-label="Table navigation">
    {#if meetings.length}
      <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
        {$translations.paginationShowing}
        <span class="font-semibold text-gray-900 dark:text-white">{startRange}-{endRange}</span>
        {$translations.paginationOf}
        <span class="font-semibold text-gray-900 dark:text-white">{filteredItems.length}</span>
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
