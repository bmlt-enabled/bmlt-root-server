<script lang="ts">
  import { createEventDispatcher } from 'svelte';
  import { Label, Select } from 'flowbite-svelte';

  import { translations } from '../stores/localization';

  export let duration: string = '00:00';

  const dispatch = createEventDispatcher();
  let [hours, minutes] = duration.split(':').map((part) => part.padStart(2, '0'));
  const hourOptions = Array.from({ length: 24 }, (_, i) => ({
    value: i.toString().padStart(2, '0'),
    name: i.toString().padStart(2, '0')
  }));
  const minuteOptions: { value: string; name: string }[] = [];
  for (let i = 0; i < 60; i = i + 5) {
    minuteOptions.push({ value: i.toString().padStart(2, '0'), name: i.toString().padStart(2, '0') });
  }
  const updateDuration = () => {
    const updatedDuration = `${hours}:${minutes}`;
    dispatch('update', { duration: updatedDuration });
  };
  const handleHourChange = (event: Event) => {
    const target = event.target as HTMLSelectElement;
    hours = target.value;
    updateDuration();
  };
  const handleMinuteChange = (event: Event) => {
    const target = event.target as HTMLSelectElement;
    minutes = target.value;
    updateDuration();
  };
</script>

<div class="flex space-x-4">
  <div class="flex flex-col">
    <Select id="hours" class="dark:bg-gray-600" items={hourOptions} bind:value={hours} on:change={handleHourChange} />
    <Label for="hours" class="mt-2 text-sm font-semibold">{$translations.hoursTitle}</Label>
  </div>
  <div class="flex flex-col">
    <Select id="minutes" class="dark:bg-gray-600" items={minuteOptions} bind:value={minutes} on:change={handleMinuteChange} />
    <Label for="minutes" class="mt-2 text-sm font-semibold">{$translations.minutesTitle}</Label>
  </div>
</div>
