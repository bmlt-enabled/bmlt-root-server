<script lang="ts">
  // svelte-hack' -- import hacked to get onMount to work correctly for unit tests
  import { onMount } from 'svelte/internal';

  import { Button, Checkbox, Dropdown, Input, Select } from 'flowbite-svelte';

  import Nav from '../components/NavBar.svelte';
  import RootServerApi from '../lib/RootServerApi';
  import { translations } from '../stores/localization';
  import { spinner } from '../stores/spinner';
  import type { Format, Meeting, ServiceBody } from 'bmlt-root-server-client';
  import MeetingsList from '../components/MeetingsList.svelte';
  import { FilterSolid } from 'flowbite-svelte-icons';

  let meetings: Meeting[] = [];
  let serviceBodies: ServiceBody[] = [];
  let formats: Format[] = [];
  let meetingsLoaded = false;
  let serviceBodiesLoaded = false;
  let formatsLoaded = false;
  let searchString = '';
  let selectedDays: string[] = [];
  let weekdayChoices = $translations.daysOfWeek.map((day, index) => ({
    value: index.toString(),
    label: day
  }));

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
    getMeetings(searchString, selectedDays.join(','));
  }

  onMount(() => {
    getFormats();
    getServiceBodies();
  });
</script>

<Nav />

<div class="mx-auto max-w-6xl p-2">
  <h2 class="mb-4 text-center text-xl font-semibold dark:text-white">{$translations.meetingsTitle}</h2>
  <div class="mb-4 flex justify-center space-x-3">
    <Input class="max-w-sm" type="text" id="default-input" placeholder={$translations.searchMeetings} bind:value={searchString} />
    <Button color="alternative">{$translations.day}<FilterSolid class="ml-2 h-3 w-3" /></Button>
    <Dropdown class="w-48 space-y-2 p-3 text-sm">
      <h6 class="mb-3 text-sm font-medium text-gray-900 dark:text-white">{$translations.chooseDay}</h6>
      <Checkbox name="weekdays" choices={weekdayChoices} bind:group={selectedDays} groupInputClass="ms-2" groupLabelClass="" />
    </Dropdown>
    <Button on:click={searchMeetings}>{$translations.search}</Button>
  </div>
  {#if meetingsLoaded && serviceBodiesLoaded && formatsLoaded}
    <MeetingsList {meetings} {serviceBodies} {formats} />
  {/if}
</div>
