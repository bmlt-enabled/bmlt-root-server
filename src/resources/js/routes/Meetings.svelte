<script lang="ts">
  // svelte-hack' -- import hacked to get onMount to work correctly for unit tests
  import { onMount } from 'svelte/internal';

  import { Button, Input, Select } from 'flowbite-svelte';

  import Nav from '../components/NavBar.svelte';
  import RootServerApi from '../lib/RootServerApi';
  import { translations } from '../stores/localization';
  import { spinner } from '../stores/spinner';
  import type { Format, Meeting, ServiceBody } from 'bmlt-root-server-client';
  import MeetingsList from '../components/MeetingsList.svelte';

  let meetings: Meeting[] = [];
  let serviceBodies: ServiceBody[] = [];
  let formats: Format[] = [];
  let meetingsLoaded = false;
  let serviceBodiesLoaded = false;
  let formatsLoaded = false;
  let searchString = '';
  let selectedDay = '';
  const weekdayChoices = [
    { value: '', name: $translations.allDays },
    ...$translations.daysOfWeek.map((day, index) => ({
      value: index,
      name: day
    }))
  ];

  async function getMeetings(searchString: string = '', days: string = ''): Promise<void> {
    try {
      spinner.show();
      meetings = await RootServerApi.getMeetings({
        searchString,
        days
      });
      meetingsLoaded = true;
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

  async function getFormats(): Promise<void> {
    try {
      spinner.show();
      formats = await RootServerApi.getFormats();
      formatsLoaded = true;
    } catch (error: any) {
      await RootServerApi.handleErrors(error);
    } finally {
      spinner.hide();
    }
  }

  function searchMeetings() {
    getMeetings(searchString, selectedDay?.toString());
  }

  onMount(() => {
    getFormats();
    getServiceBodies();
  });
</script>

<Nav />

<div class="mx-auto max-w-6xl p-2">
  <h2 class="mb-4 text-center text-xl font-semibold dark:text-white">{$translations.meetingsTitle}</h2>
  <div class="mb-4 flex space-x-3">
    <Input type="text" id="default-input" placeholder={$translations.searchMeetings} bind:value={searchString} />
    <Select id="day" items={weekdayChoices} bind:value={selectedDay} name="day" class="dark:bg-gray-600" />
    <Button on:click={searchMeetings}>{$translations.search}</Button>
  </div>
  {#if meetingsLoaded && serviceBodiesLoaded && formatsLoaded}
    <MeetingsList {meetings} {serviceBodies} {formats} />
  {/if}
</div>
