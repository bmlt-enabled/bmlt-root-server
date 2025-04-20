<script lang="ts">
  import { Label, Select } from 'flowbite-svelte';

  import { translations } from '../stores/localization';

  // Better style with svelte 5 would be to just have one prop 'duration' and make it bindable.
  // However, felte doesn't know about svelte 5 (yet), so this wouldn't work currently.
  interface Props {
    updateDuration: (d: string) => void;
    initialDuration?: string;
  }

  let { updateDuration, initialDuration = '00:00' }: Props = $props();

  let [hours, minutes] = $state(initialDuration.split(':').map((part) => part.padStart(2, '0')));
  const hourOptions = Array.from({ length: 24 }, (_, i) => ({
    value: i.toString().padStart(2, '0'),
    name: i.toString().padStart(2, '0')
  }));
  const minuteOptions: { value: string; name: string }[] = [];
  for (let i = 0; i < 60; i = i + 5) {
    minuteOptions.push({ value: i.toString().padStart(2, '0'), name: i.toString().padStart(2, '0') });
  }
  const handleHourChange = (event: Event) => {
    const target = event.target as HTMLSelectElement;
    hours = target.value;
    updateDurationHelper();
  };
  const handleMinuteChange = (event: Event) => {
    const target = event.target as HTMLSelectElement;
    minutes = target.value;
    updateDurationHelper();
  };
  const updateDurationHelper = () => {
    updateDuration(`${hours}:${minutes}`);
  };
</script>

<div class="flex space-x-4">
  <div class="flex flex-col">
    <Select id="hours" class="dark:bg-gray-600" items={hourOptions} bind:value={hours} onchange={handleHourChange} />
    <Label for="hours" class="mt-2 text-sm font-semibold">{$translations.hoursTitle}</Label>
  </div>
  <div class="flex flex-col">
    <Select id="minutes" class="dark:bg-gray-600" items={minuteOptions} bind:value={minutes} onchange={handleMinuteChange} />
    <Label for="minutes" class="mt-2 text-sm font-semibold">{$translations.minutesTitle}</Label>
  </div>
</div>
