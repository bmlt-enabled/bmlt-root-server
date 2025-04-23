<script lang="ts">
  import { validator } from '@felte/validator-yup';
  import { createForm } from 'felte';
  import { Button, Checkbox, Hr, Label, Input, Helper, Select, MultiSelect, Badge } from 'flowbite-svelte';
  import * as yup from 'yup';
  import L from 'leaflet';
  import type { DragEndEvent, Map, Marker } from 'leaflet';
  import { Loader } from '@googlemaps/js-api-loader';
  import { writable } from 'svelte/store';

  const showMap = writable(false);
  import DurationSelector from './DurationSelector.svelte';
  import MapAccordion from './MapAccordion.svelte';
  import BasicTabs from './BasicTabs.svelte';

  import { onMount } from 'svelte';
  import { spinner } from '../stores/spinner';
  import type { MeetingChangeResource } from 'bmlt-root-server-client';
  import RootServerApi from '../lib/RootServerApi';
  import { formIsDirty } from '../lib/utils';
  import { timeZones } from '../lib/timeZone/timeZones';
  import { tzFind } from '../lib/timeZone/find';
  import { Geocoder } from '../lib/geocoder';
  import type { Format, Meeting, MeetingPartialUpdate, ServiceBody } from 'bmlt-root-server-client';
  import { translations } from '../stores/localization';
  import MeetingDeleteModal from './MeetingDeleteModal.svelte';
  import { TrashBinOutline } from 'flowbite-svelte-icons';

  interface Props {
    selectedMeeting: Meeting | null;
    serviceBodies: ServiceBody[];
    formats: Format[];
    onSaved: (meeting: Meeting) => void;
    onClosed: () => void;
    onDeleted: (meeting: Meeting) => void;
  }

  let { selectedMeeting, serviceBodies, formats, onSaved, onClosed: _, onDeleted }: Props = $props();

  const tabs = selectedMeeting
    ? [$translations.tabsBasic, $translations.tabsLocation, $translations.tabsOther, $translations.tabsChanges]
    : [$translations.tabsBasic, $translations.tabsLocation, $translations.tabsOther];
  const TAB_CHANGES = 3;
  const globalSettings = settings;
  const seenNames = new Set<string>();
  const ignoredFormats = ['VM', 'HY', 'TC'];
  const filteredFormats = formats
    .map((format) => {
      const translation = format.translations.find((t) => t.language === translations.getLanguage());
      if (translation) {
        return {
          id: format.id,
          type: format.type,
          worldId: format.worldId,
          ...translation
        };
      }
      return null;
    })
    .filter((format) => {
      if (!format) return false;
      if (ignoredFormats.some((ignored) => format.key.includes(ignored))) {
        return false;
      }
      if (seenNames.has(format.name)) return false;
      seenNames.add(format.name);
      return true;
    });

  const formatItems = filteredFormats
    .filter((f): f is NonNullable<typeof f> => f !== null)
    .map((f) => ({ value: f.id, name: f.name }))
    .sort((a, b) => a.name.localeCompare(b.name));

  const serviceBodyIdItems = serviceBodies.map((u) => ({ value: u.id, name: u.name })).sort((a, b) => a.name.localeCompare(b.name));
  const VENUE_TYPE_IN_PERSON = 1;
  const VENUE_TYPE_VIRTUAL = 2;
  const VENUE_TYPE_HYBRID = 3;
  const VALID_VENUE_TYPES = [VENUE_TYPE_IN_PERSON, VENUE_TYPE_VIRTUAL, VENUE_TYPE_HYBRID];

  let map: google.maps.Map | L.Map | undefined = $state();
  let mapElement: HTMLElement | undefined = $state();
  let marker: google.maps.marker.AdvancedMarkerElement | L.Marker | null = $state(null);
  let geocodingError: string | null = $state(null);
  let isPublishedChecked = $state(true);
  let showDeleteModal = $state(false);
  let meetingToDelete: Meeting | undefined = $state();
  const weekdayChoices = $translations.daysOfWeek.map((day: string, index: number) => ({
    value: index,
    name: day
  }));
  const statesAndProvincesChoices = globalSettings.meetingStatesAndProvinces
    .map((state) => ({
      value: state,
      name: state
    }))
    .sort((a, b) => a.name.localeCompare(b.name));
  const countiesAndSubProvincesChoices = globalSettings.meetingCountiesAndSubProvinces
    .map((county) => ({
      value: county,
      name: county
    }))
    .sort((a, b) => a.name.localeCompare(b.name));
  const venueTypeItems = [
    { value: VENUE_TYPE_IN_PERSON, name: 'In-Person' },
    { value: VENUE_TYPE_VIRTUAL, name: 'Virtual' },
    { value: VENUE_TYPE_HYBRID, name: 'Hybrid' }
  ];
  const timeZoneChoices = timeZones.map((tz) => ({
    value: tz,
    name: tz
  }));

  const defaultLatLng = { lat: Number(globalSettings.centerLatitude ?? -79.793701171875), lng: Number(globalSettings.centerLongitude ?? 36.065752051707) };
  let defaultDuration = '01:00';
  // older autoconfig files store the default duration including seconds -- remove the seconds if needed for compatibility
  if (globalSettings.defaultDuration) {
    const [hours, minutes] = globalSettings.defaultDuration.split(':').map((part) => part.padStart(2, '0'));
    defaultDuration = hours + ':' + minutes;
  }
  const initialValues = {
    serviceBodyId: selectedMeeting?.serviceBodyId ?? -1,
    formatIds: selectedMeeting?.formatIds ?? [],
    venueType: selectedMeeting?.venueType ?? VENUE_TYPE_IN_PERSON,
    temporarilyVirtual: selectedMeeting?.temporarilyVirtual ?? false,
    day: selectedMeeting?.day ?? 0,
    startTime: selectedMeeting?.startTime ?? '12:00',
    duration: selectedMeeting?.duration ?? defaultDuration,
    timeZone: selectedMeeting?.timeZone ?? '',
    latitude: selectedMeeting?.latitude ?? defaultLatLng.lat,
    longitude: selectedMeeting?.longitude ?? defaultLatLng.lng,
    published: selectedMeeting?.published ?? true,
    email: selectedMeeting?.email ?? '',
    worldId: selectedMeeting?.worldId ?? '',
    name: selectedMeeting?.name ?? '',
    locationText: selectedMeeting?.locationText ?? '',
    locationInfo: selectedMeeting?.locationInfo ?? '',
    locationStreet: selectedMeeting?.locationStreet ?? '',
    locationNeighborhood: selectedMeeting?.locationNeighborhood ?? '',
    locationCitySubsection: selectedMeeting?.locationCitySubsection ?? '',
    locationMunicipality: selectedMeeting?.locationMunicipality ?? '',
    locationSubProvince: selectedMeeting?.locationSubProvince ?? '',
    locationProvince: selectedMeeting?.locationProvince ?? '',
    locationPostalCode1: selectedMeeting?.locationPostalCode1 ?? '',
    locationNation: selectedMeeting?.locationNation ?? '',
    phoneMeetingNumber: selectedMeeting?.phoneMeetingNumber ?? '',
    virtualMeetingLink: selectedMeeting?.virtualMeetingLink ?? '',
    virtualMeetingAdditionalInfo: selectedMeeting?.virtualMeetingAdditionalInfo ?? '',
    contactName1: selectedMeeting?.contactName1 ?? '',
    contactName2: selectedMeeting?.contactName2 ?? '',
    contactPhone1: selectedMeeting?.contactPhone1 ?? '',
    contactPhone2: selectedMeeting?.contactPhone2 ?? '',
    contactEmail1: selectedMeeting?.contactEmail1 ?? '',
    contactEmail2: selectedMeeting?.contactEmail2 ?? '',
    busLines: selectedMeeting?.busLines ?? '',
    trainLines: selectedMeeting?.trainLines ?? '',
    comments: selectedMeeting?.comments ?? '',
    customFields: selectedMeeting?.customFields
      ? {
          ...Object.fromEntries(globalSettings.customFields.map((field) => [field.name, ''])),
          ...Object.fromEntries(Object.entries(selectedMeeting.customFields).map(([key, value]) => [key, value ?? '']))
        }
      : Object.fromEntries(globalSettings.customFields.map((field) => [field.name, '']))
  };
  let latitude = $state(initialValues.latitude);
  let longitude = $state(initialValues.longitude);
  let manualDrag = false;
  let formatIdsSelected = $state(initialValues.formatIds);
  let savedMeeting: Meeting;
  let changes: MeetingChangeResource[] = $state([]);
  let changesLoaded = $state(false);

  function shouldGeocode(initialValues: MeetingPartialUpdate, values: MeetingPartialUpdate, isNewMeeting: boolean) {
    if (isNewMeeting && values.venueType != VENUE_TYPE_VIRTUAL) {
      return true;
    }

    return (
      initialValues.locationStreet !== values.locationStreet ||
      initialValues.locationCitySubsection !== values.locationCitySubsection ||
      initialValues.locationMunicipality !== values.locationMunicipality ||
      initialValues.locationProvince !== values.locationProvince ||
      initialValues.locationSubProvince !== values.locationSubProvince
    );
  }

  async function handleGeocoding(values: MeetingPartialUpdate) {
    const geocoder = new Geocoder(values);
    const geocodeResult = await geocoder.geocode();
    if (typeof geocodeResult === 'string') {
      geocodingError = geocodeResult;
      spinner.hide();
      throw new Error(geocodeResult);
    }
    if (geocodeResult) {
      values.latitude = geocodeResult.lat;
      values.longitude = geocodeResult.lng;
      if (globalSettings.countyAutoGeocodingEnabled) {
        values.locationSubProvince = geocodeResult.county;
      }
      if (globalSettings.zipAutoGeocodingEnabled) {
        values.locationPostalCode1 = geocodeResult.zipCode;
      }
    }
  }

  const { data, errors, form, setData, isDirty } = createForm({
    initialValues: initialValues,
    onSubmit: async (values) => {
      spinner.show();
      const isNewMeeting = !selectedMeeting;
      if (shouldGeocode(initialValues, values, isNewMeeting)) {
        if (globalSettings.autoGeocodingEnabled && !manualDrag) {
          await handleGeocoding(values);
        }
      }

      if (!values.timeZone && values.latitude && values.longitude) {
        let tzData = await tzFind(values.latitude, values.longitude);
        if (tzData.length > 0) {
          values.timeZone = tzData[0];
        }
      }

      if (selectedMeeting) {
        await RootServerApi.updateMeeting(selectedMeeting.id, values);
        savedMeeting = await RootServerApi.getMeeting(selectedMeeting.id);
      } else {
        savedMeeting = await RootServerApi.createMeeting(values);
      }
    },
    onError: async (error) => {
      console.log(error);
      await RootServerApi.handleErrors(error as Error, {
        handleValidationError: (error) => {
          errors.set({
            serviceBodyId: (error?.errors?.serviceBodyId ?? []).join(' '),
            formatIds: (error?.errors?.formatIds ?? []).join(' '),
            venueType: (error?.errors?.venueType ?? []).join(' '),
            temporarilyVirtual: (error?.errors?.temporarilyVirtual ?? []).join(' '),
            day: (error?.errors?.day ?? []).join(' '),
            startTime: (error?.errors?.startTime ?? []).join(' '),
            duration: (error?.errors?.duration ?? []).join(' '),
            timeZone: (error?.errors?.timeZone ?? []).join(' '),
            latitude: (error?.errors?.latitude ?? []).join(' '),
            longitude: (error?.errors?.longitude ?? []).join(' '),
            published: (error?.errors?.published ?? []).join(' '),
            email: (error?.errors?.email ?? []).join(' '),
            worldId: (error?.errors?.worldId ?? []).join(' '),
            name: (error?.errors?.name ?? []).join(' '),
            locationText: (error?.errors?.location_text ?? []).join(' '),
            locationInfo: (error?.errors?.location_info ?? []).join(' '),
            locationStreet: (error?.errors?.location_street ?? []).join(' '),
            locationNeighborhood: (error?.errors?.location_neighborhood ?? []).join(' '),
            locationCitySubsection: (error?.errors?.location_city_subsection ?? []).join(' '),
            locationMunicipality: (error?.errors?.location_municipality ?? []).join(' '),
            locationSubProvince: (error?.errors?.location_sub_province ?? []).join(' '),
            locationProvince: (error?.errors?.location_province ?? []).join(' '),
            locationPostalCode1: (error?.errors?.location_postal_code_1 ?? []).join(' '),
            locationNation: (error?.errors?.location_nation ?? []).join(' '),
            phoneMeetingNumber: (error?.errors?.phone_meeting_number ?? []).join(' '),
            virtualMeetingLink: (error?.errors?.virtual_meeting_link ?? []).join(' '),
            virtualMeetingAdditionalInfo: (error?.errors?.virtual_meeting_additional_info ?? []).join(' '),
            contactName1: (error?.errors?.contact_name_1 ?? []).join(' '),
            contactName2: (error?.errors?.contact_name_2 ?? []).join(' '),
            contactPhone1: (error?.errors?.contact_phone_1 ?? []).join(' '),
            contactPhone2: (error?.errors?.contact_phone_2 ?? []).join(' '),
            contactEmail1: (error?.errors?.contact_email_1 ?? []).join(' '),
            contactEmail2: (error?.errors?.contact_email_2 ?? []).join(' '),
            busLines: (error?.errors?.bus_lines ?? []).join(' '),
            trainLines: (error?.errors?.train_lines ?? []).join(' '),
            comments: (error?.errors?.comments ?? []).join(' '),
            customFields: error?.errors?.customFields ? Object.fromEntries(Object.entries(error.errors.customFields).map(([key, value]) => [key, Array.isArray(value) ? value.join(' ') : value])) : {}
          });
        }
      });
      spinner.hide();
    },
    onSuccess: () => {
      spinner.hide();
      onSaved(savedMeeting);
    },
    extend: validator({
      schema: yup.object({
        serviceBodyId: yup
          .number()
          .transform((v) => parseInt(v))
          .required(),
        formatIds: yup.array().of(yup.number()),
        venueType: yup.number().oneOf(VALID_VENUE_TYPES).required(),
        temporarilyVirtual: yup.bool(),
        day: yup.number().integer().min(0).max(6).required(),
        startTime: yup
          .string()
          .matches(/^([0-1]\d|2[0-3]):([0-5]\d)$/)
          .required(), // HH:mm
        duration: yup
          .string()
          .matches(/^([0-1]\d|2[0-3]):([0-5]\d)$/)
          .required(), // HH:mm
        timeZone: yup
          .string()
          .oneOf([...timeZones, ''], 'Invalid time zone')
          .max(40),
        latitude: yup.number().min(-90).max(90).required(),
        longitude: yup.number().min(-180).max(180).required(),
        published: yup.bool().required(),
        email: yup.string().max(255).email(),
        worldId: yup
          .string()
          .transform((v) => v.trim())
          .max(30),
        name: yup
          .string()
          .transform((v) => v.trim())
          .max(128)
          .required(),
        locationText: yup.string().transform((v) => v.trim()),
        locationInfo: yup.string().transform((v) => v.trim()),
        locationStreet: yup
          .string()
          .default('')
          .transform((v) => v.trim())
          .max(255)
          .when('venueType', {
            is: (venueType: number) => [VENUE_TYPE_IN_PERSON, VENUE_TYPE_HYBRID].includes(venueType),
            then: (schema) => schema.required($translations.locationStreetErrorMessage),
            otherwise: (schema) => schema.notRequired()
          }),
        locationNeighborhood: yup.string().transform((v) => v.trim()),
        locationCitySubsection: yup.string().transform((v) => v.trim()),
        locationMunicipality: yup.string().transform((v) => v.trim()),
        locationSubProvince: yup.string().transform((v) => v.trim()),
        locationProvince: yup.string().transform((v) => v.trim()),
        locationPostalCode1: yup.string().transform((v) => v.trim()),
        locationNation: yup.string().transform((v) => v.trim()),
        phoneMeetingNumber: yup.string().transform((v) => v.trim()),
        virtualMeetingLink: yup
          .string()
          .transform((v) => v.trim())
          .url(),
        virtualMeetingAdditionalInfo: yup.string().transform((v) => v.trim()),
        contactName1: yup.string().transform((v) => v.trim()),
        contactName2: yup.string().transform((v) => v.trim()),
        contactPhone1: yup.string().transform((v) => v.trim()),
        contactPhone2: yup.string().transform((v) => v.trim()),
        contactEmail1: yup
          .string()
          .transform((v) => v.trim())
          .email(),
        contactEmail2: yup
          .string()
          .transform((v) => v.trim())
          .email(),
        busLines: yup.string().transform((v) => v.trim()),
        trainLines: yup.string().transform((v) => v.trim()),
        comments: yup.string().transform((v) => v.trim())
      }),
      castValues: true
    })
  });

  const formatIdToFormatType = Object.fromEntries(filteredFormats.map((f) => [f?.id, f]));
  function badgeColor(id: string) {
    if (formatIdToFormatType[id].type === 'MEETING_FORMAT') {
      return 'green';
    } else if (formatIdToFormatType[id].type === 'OPEN_OR_CLOSED') {
      return 'red';
    } else {
      return 'yellow';
    }
  }

  function handleDelete(event: MouseEvent, meeting: Meeting) {
    event.stopPropagation();
    meetingToDelete = meeting;
    showDeleteModal = true;
  }

  // This hack is required until https://github.com/themesberg/flowbite-svelte/issues/1395 is fixed.
  function disableButtonHack(event: MouseEvent) {
    if (!$isDirty) {
      event.preventDefault();
    }
  }

  // Type guards
  function isGoogleMarker(marker: google.maps.marker.AdvancedMarkerElement | Marker): marker is google.maps.marker.AdvancedMarkerElement {
    return (marker as google.maps.marker.AdvancedMarkerElement).position !== undefined;
  }

  function isGoogleMap(map: google.maps.Map | Map): map is google.maps.Map {
    return (map as google.maps.Map).setCenter !== undefined;
  }

  function isLeafletMap(map: google.maps.Map | Map): map is Map {
    return (map as Map).setView !== undefined;
  }

  function setMapCenter(map: google.maps.Map | L.Map, lat: number, lng: number) {
    if (isGoogleMap(map)) {
      map.setCenter({ lat, lng });
    } else if (isLeafletMap(map)) {
      map.setView([lat, lng]);
    }
  }

  function createLeafletMap() {
    map = L.map(mapElement).setView([latitude, longitude], Number(globalSettings.centerZoom ?? 18));
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 22,
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    const naMarkerImage = L.icon({
      iconUrl: 'images/NAMarkerR.png',
      iconSize: [44, 64]
    });

    marker = L.marker([latitude, longitude], { icon: naMarkerImage, draggable: true }).addTo(map);

    marker.on('dragend', (e: DragEndEvent) => {
      const { lat, lng } = e.target.getLatLng();
      latitude = lat;
      longitude = lng;
      manualDrag = true;
    });
  }

  async function createGoogleMap() {
    const loader = new Loader({
      apiKey: globalSettings.googleApiKey,
      version: 'beta',
      libraries: ['places', 'marker', 'geocoding']
    });
    const { Map } = await loader.importLibrary('maps');
    mapElement = document.getElementById('locationMap') as HTMLElement;
    if (mapElement) {
      map = new Map(mapElement, {
        center: { lat: latitude, lng: longitude },
        zoom: Number(globalSettings.centerZoom ?? 18),
        draggableCursor: 'crosshair',
        mapId: 'bmlt'
      });
      createGoogleMarker();
    }
  }

  function createGoogleMarker() {
    const position = { lat: latitude, lng: longitude };
    const naMarkerImage = document.createElement('img');
    naMarkerImage.src = 'images/NAMarkerR.png';
    if (isGoogleMap(map)) {
      marker = new google.maps.marker.AdvancedMarkerElement({
        position: position,
        map: map,
        gmpDraggable: true,
        content: naMarkerImage
      });
      marker.addListener('dragend', () => {
        if (marker && isGoogleMarker(marker) && marker.position) {
          const newPosition = marker.position;
          if (newPosition) {
            longitude = typeof newPosition.lng === 'function' ? newPosition.lng() : newPosition.lng;
            latitude = typeof newPosition.lat === 'function' ? newPosition.lat() : newPosition.lat;
            setData('longitude', longitude);
            setData('latitude', latitude);
            manualDrag = true;
          }
        }
      });
    }
  }

  async function getChanges(meetingId: number): Promise<void> {
    try {
      spinner.show();
      changes = await RootServerApi.getMeetingChanges(meetingId);
      changesLoaded = true;
    } catch (error: any) {
      await RootServerApi.handleErrors(error);
    } finally {
      spinner.hide();
    }
  }

  function handleTabChange(index: number) {
    if (TAB_CHANGES === index && selectedMeeting) getChanges(selectedMeeting.id);
  }

  function hasBasicErrors(errors: any): boolean {
    return Boolean(
      errors.published ||
        errors.name ||
        errors.timeZone ||
        errors.day ||
        errors.startTime ||
        errors.duration ||
        errors.serviceBodyId ||
        errors.email ||
        errors.worldId ||
        errors.formatIds?.map((f: any) => Object.keys(f).length).find((n: number) => n > 0)
    );
  }

  function hasLocationErrors(errors: any): boolean {
    return Boolean(
      errors.venueType ||
        errors.temporarilyVirtual ||
        errors.longitude ||
        errors.latitude ||
        errors.locationText ||
        errors.locationInfo ||
        errors.locationStreet ||
        errors.locationNeighborhood ||
        errors.locationCitySubsection ||
        errors.locationMunicipality ||
        errors.locationSubProvince ||
        errors.locationProvince ||
        errors.locationPostalCode1 ||
        errors.locationNation ||
        errors.locationPostalCode1 ||
        errors.locationNation ||
        errors.phoneMeetingNumber ||
        errors.virtualMeetingLink ||
        errors.virtualMeetingAdditionalInfo
    );
  }

  function hasOtherErrors(errors: any): boolean {
    return Boolean(
      errors.comments ||
        errors.busLines ||
        errors.trainLines ||
        errors.contactName1 ||
        errors.contactName2 ||
        errors.contactPhone1 ||
        errors.contactPhone2 ||
        errors.contactEmail1 ||
        errors.contactEmail2
    );
  }

  let errorTabs: string[] = $derived((hasBasicErrors($errors) ? [tabs[0]] : []).concat(hasLocationErrors($errors) ? [tabs[1]] : []).concat(hasOtherErrors($errors) ? [tabs[2]] : []));

  onMount(() => {
    mapElement = document.getElementById('locationMap') as HTMLElement;
    if (mapElement) {
      if (globalSettings.googleApiKey) {
        createGoogleMap();
      } else {
        createLeafletMap();
      }

      showMap.subscribe((value) => {
        mapElement.style.display = value ? 'block' : 'none';
        if (value && map) {
          setMapCenter(map, latitude, longitude);
        }
      });
    }
  });

  // TODO: the following 3 uses of $effect were converted from $: in the svelte 4 version of the code.  They
  // probably should use $derived or something else instead, since they have side effects.
  $effect(() => {
    if (selectedMeeting) {
      showMap.set(true);
      manualDrag = false;
    }
  });

  $effect(() => {
    setData('formatIds', formatIdsSelected);
  });
  $effect(() => {
    isDirty.set(formIsDirty(initialValues, $data));
  });
</script>

<svelte:head>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.1/dist/leaflet.css" />
</svelte:head>

{#snippet basicTabContent()}
  <div class="grid items-center gap-4 md:grid-cols-3">
    <div class="w-full">
      <Checkbox name="published" bind:checked={isPublishedChecked}>
        {$translations.meetingIsPublishedTitle}
      </Checkbox>
      {#if !isPublishedChecked}
        <Helper class="mt-2" color="red">
          {$translations.meetingUnpublishedNote}
        </Helper>
      {/if}
      {#if $errors.published}
        <Helper class="mt-2" color="red">
          {$errors.published}
        </Helper>
      {/if}
    </div>
    {#if selectedMeeting}
      <div class="flex w-full items-center justify-between md:col-span-2">
        <div class="text-gray-700 dark:text-gray-300">
          <strong>Meeting ID:</strong>
          {selectedMeeting.id}
        </div>
        <Button
          color="none"
          onclick={(e: MouseEvent) => selectedMeeting && handleDelete(e, selectedMeeting)}
          class="text-red-600 dark:text-red-500"
          aria-label={$translations.deleteMeeting + ' ' + (selectedMeeting?.id ?? '')}
        >
          <TrashBinOutline title={{ id: 'deleteMeeting', title: $translations.deleteMeeting }} ariaLabel={$translations.deleteMeeting} />
          <span class="sr-only">{$translations.deleteMeeting}</span>
        </Button>
      </div>
    {/if}
  </div>
  <div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
      <Label for="name" class="mb-2 mt-2">{$translations.nameTitle}</Label>
      <Input type="text" id="name" name="name" required />
      {#if $errors.name}
        <Helper class="mt-2" color="red">
          {$errors.name}
        </Helper>
      {/if}
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
      <Label for="timeZone" class="mb-2 mt-2">{$translations.timeZoneTitle}</Label>
      <Select id="timeZone" items={timeZoneChoices} name="timeZone" class="dark:bg-gray-600" placeholder={$translations.timeZoneSelectPlaceholder} />
      {#if $errors.timeZone}
        <Helper class="mt-2" color="red">
          {$errors.timeZone}
        </Helper>
      {/if}
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-3">
    <div class="w-full">
      <Label for="day" class="mb-2 mt-2">{$translations.dayTitle}</Label>
      <Select id="day" items={weekdayChoices} name="day" class="dark:bg-gray-600" />
      {#if $errors.day}
        <Helper class="mt-2" color="red">
          {$errors.day}
        </Helper>
      {/if}
    </div>
    <div class="w-full">
      <Label for="startTime" class="mb-2 mt-2">{$translations.startTimeTitle}</Label>
      <Input type="time" id="startTime" name="startTime" />
      {#if $errors.startTime}
        <Helper class="mt-2" color="red">
          {$errors.startTime}
        </Helper>
      {/if}
    </div>
    <div class="w-full">
      <span class="mb-2 mt-2 block text-sm font-medium text-gray-900 rtl:text-right dark:text-gray-300">{$translations.durationTitle}</span>
      <DurationSelector initialDuration={initialValues.duration} updateDuration={(d: string) => setData('duration', d)} />
      {#if $errors.duration}
        <Helper class="mt-2" color="red">
          {$errors.duration}
        </Helper>
      {/if}
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
      <Label for="serviceBodyId" class="mb-2 mt-2">{$translations.serviceBodyTitle}</Label>
      <Select id="serviceBodyId" items={serviceBodyIdItems} name="serviceBodyId" class="dark:bg-gray-600" />
      {#if $errors.serviceBodyId}
        <Helper class="mt-2" color="red">
          {$errors.serviceBodyId}
        </Helper>
      {/if}
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-2">
    <div class="w-full">
      <Label for="email" class="mb-2 mt-2">{$translations.emailTitle}</Label>
      <Input type="email" id="email" name="email" />
      {#if $errors.email}
        <Helper class="mt-2" color="red">
          {$errors.email}
        </Helper>
      {/if}
    </div>
    <div class="w-full">
      <Label for="worldId" class="mb-2 mt-2">{$translations.worldIdTitle}</Label>
      <Input type="text" id="worldId" name="worldId" />
      {#if $errors.worldId}
        <Helper class="mt-2" color="red">
          {$errors.worldId}
        </Helper>
      {/if}
    </div>
  </div>
  <div class="md:col-span-2">
    <Label for="formatIds" class="mb-2 mt-2">{$translations.formatsTitle}</Label>
    <MultiSelect id="formatIds" items={formatItems} name="formatIds" class="bg-gray-50 dark:bg-gray-600" bind:value={formatIdsSelected} let:item let:clear>
      <Badge rounded color={badgeColor(item.value)} dismissable params={{ duration: 100 }} on:close={clear}>
        {item.name}
      </Badge>
    </MultiSelect>
    <!-- For some reason yup fills the errors store with empty objects for this array. The === 'string' ensures only server side errors will display. -->
    {#if $errors.formatIds && typeof $errors.formatIds[0] === 'string'}
      <Helper class="mt-2" color="red">
        {$errors.formatIds}
      </Helper>
    {/if}
  </div>
{/snippet}

{#snippet locationTabContent()}
  <div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
      <Label for="venueType" class="mb-2 mt-2">{$translations.venueTypeTitle}</Label>
      <Select id="venueType" items={venueTypeItems} name="venueType" class="dark:bg-gray-600" />
      {#if $errors.venueType}
        <Helper class="mt-2" color="red">
          {$errors.venueType}
        </Helper>
      {/if}
    </div>
  </div>
  {#if selectedMeeting}
    <div class="grid gap-4 md:grid-cols-2">
      <div class="md:col-span-2">
        <MapAccordion title={$translations.locationMapTitle} {map}>
          <div id="locationMap" bind:this={mapElement}></div>
        </MapAccordion>
      </div>
    </div>
  {/if}
  <div class="grid gap-4 md:grid-cols-2">
    <div class="w-full">
      <Label for="longitude" class="mb-2 mt-2">{$translations.longitudeTitle}</Label>
      <Input type="text" id="longitude" name="longitude" bind:value={longitude} required />
      {#if $errors.longitude}
        <Helper class="mt-2" color="red">
          {$errors.longitude}
        </Helper>
      {/if}
    </div>
    <div class="w-full">
      <Label for="latitude" class="mb-2 mt-2">{$translations.latitudeTitle}</Label>
      <Input type="text" id="latitude" name="latitude" bind:value={latitude} required />
      {#if $errors.latitude}
        <Helper class="mt-2" color="red">
          {$errors.latitude}
        </Helper>
      {/if}
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
      <Label for="locationText" class="mb-2 mt-2">{$translations.locationTextTitle}</Label>
      <Input type="text" id="locationText" name="locationText" />
      {#if $errors.locationText}
        <Helper class="mt-2" color="red">
          {$errors.locationText}
        </Helper>
      {/if}
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
      <Label for="locationInfo" class="mb-2 mt-2">{$translations.extraInfoTitle}</Label>
      <Input type="text" id="locationInfo" name="locationInfo" />
      {#if $errors.locationInfo}
        <Helper class="mt-2" color="red">
          {$errors.locationInfo}
        </Helper>
      {/if}
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
      <Label for="locationStreet" class="mb-2 mt-2">{$translations.streetTitle}</Label>
      <Input type="text" id="locationStreet" name="locationStreet" />
      {#if $errors.locationStreet}
        <Helper class="mt-2" color="red">
          {$errors.locationStreet}
        </Helper>
      {/if}
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-2">
    <div class="w-full">
      <Label for="locationNeighborhood" class="mb-2 mt-2">{$translations.neighborhoodTitle}</Label>
      <Input type="text" id="locationNeighborhood" name="locationNeighborhood" />
      {#if $errors.locationNeighborhood}
        <Helper class="mt-2" color="red">
          {$errors.locationNeighborhood}
        </Helper>
      {/if}
    </div>
    <div class="w-full">
      <Label for="locationCitySubsection" class="mb-2 mt-2">{$translations.boroughTitle}</Label>
      <Input type="text" id="locationCitySubsection" name="locationCitySubsection" />
      {#if $errors.locationCitySubsection}
        <Helper class="mt-2" color="red">
          {$errors.locationCitySubsection}
        </Helper>
      {/if}
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-2">
    <div class="w-full">
      <Label for="locationMunicipality" class="mb-2 mt-2">{$translations.cityTownTitle}</Label>
      <Input type="text" id="locationMunicipality" name="locationMunicipality" />
      {#if $errors.locationMunicipality}
        <Helper class="mt-2" color="red">
          {$errors.locationMunicipality}
        </Helper>
      {/if}
    </div>
    <div class="w-full">
      <Label for="locationSubProvince" class="mb-2 mt-2">{$translations.countySubProvinceTitle}</Label>
      {#if countiesAndSubProvincesChoices.length > 0}
        <Select id="locationSubProvince" items={countiesAndSubProvincesChoices} name="locationSubProvince" class="dark:bg-gray-600" />
      {:else}
        <Input type="text" id="locationSubProvince" name="locationSubProvince" />
      {/if}
      {#if $errors.locationSubProvince}
        <Helper class="mt-2" color="red">
          {$errors.locationSubProvince}
        </Helper>
      {/if}
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-3">
    <div class="w-full">
      <Label for="locationProvince" class="mb-2 mt-2">{$translations.stateTitle}</Label>
      {#if statesAndProvincesChoices.length > 0}
        <Select id="locationProvince" items={statesAndProvincesChoices} name="locationProvince" class="dark:bg-gray-600" />
      {:else}
        <Input type="text" id="locationProvince" name="locationProvince" />
      {/if}
      {#if $errors.locationProvince}
        <Helper class="mt-2" color="red">
          {$errors.locationProvince}
        </Helper>
      {/if}
    </div>
    <div class="w-full">
      <Label for="locationPostalCode1" class="mb-2 mt-2">{$translations.zipCodeTitle}</Label>
      <Input type="text" id="locationPostalCode1" name="locationPostalCode1" />
      {#if $errors.locationPostalCode1}
        <Helper class="mt-2" color="red">
          {$errors.locationPostalCode1}
        </Helper>
      {/if}
    </div>
    <div class="w-full">
      <Label for="locationNation" class="mb-2 mt-2">{$translations.nationTitle}</Label>
      <Input type="text" id="locationNation" name="locationNation" />
      {#if $errors.locationNation}
        <Helper class="mt-2" color="red">
          {$errors.locationNation}
        </Helper>
      {/if}
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
      <Label for="phoneMeetingNumber" class="mb-2 mt-2">{$translations.phoneMeetingTitle}</Label>
      <Input type="text" id="phoneMeetingNumber" name="phoneMeetingNumber" />
      {#if $errors.phoneMeetingNumber}
        <Helper class="mt-2" color="red">
          {$errors.phoneMeetingNumber}
        </Helper>
      {/if}
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
      <Label for="virtualMeetingLink" class="mb-2 mt-2">{$translations.virtualMeetingTitle}</Label>
      <Input type="text" id="virtualMeetingLink" name="virtualMeetingLink" />
      {#if $errors.virtualMeetingLink}
        <Helper class="mt-2" color="red">
          {$errors.virtualMeetingLink}
        </Helper>
      {/if}
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
      <Label for="virtualMeetingAdditionalInfo" class="mb-2 mt-2">{$translations.virtualMeetingAdditionalInfoTitle}</Label>
      <Input type="text" id="virtualMeetingAdditionalInfo" name="virtualMeetingAdditionalInfo" />
      {#if $errors.virtualMeetingAdditionalInfo}
        <Helper class="mt-2" color="red">
          {$errors.virtualMeetingAdditionalInfo}
        </Helper>
      {/if}
    </div>
  </div>
{/snippet}

{#snippet otherTabContent()}
  <div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
      <Label for="comments" class="mb-2 mt-2">{$translations.commentsTitle}</Label>
      <Input type="text" id="comments" name="comments" />
      {#if $errors.comments}
        <Helper class="mt-2" color="red">
          {$errors.comments}
        </Helper>
      {/if}
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-2">
    <div class="w-full">
      <Label for="busLines" class="mb-2 mt-2">{$translations.busLinesTitle}</Label>
      <Input type="text" id="busLines" name="busLines" />
      {#if $errors.busLines}
        <Helper class="mt-2" color="red">
          {$errors.busLines}
        </Helper>
      {/if}
    </div>
    <div class="w-full">
      <Label for="trainLines" class="mb-2 mt-2">{$translations.trainLinesTitle}</Label>
      <Input type="text" id="trainLines" name="trainLines" />
      {#if $errors.trainLines}
        <Helper class="mt-2" color="red">
          {$errors.trainLines}
        </Helper>
      {/if}
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-3">
    <div class="w-full">
      <Label for="contactName1" class="mb-2 mt-2">{$translations.contact1NameTitle}</Label>
      <Input type="text" id="contactName1" name="contactName1" />
      {#if $errors.contactName1}
        <Helper class="mt-2" color="red">
          {$errors.contactName1}
        </Helper>
      {/if}
    </div>
    <div class="w-full">
      <Label for="contactPhone1" class="mb-2 mt-2">{$translations.contact1PhoneTitle}</Label>
      <Input type="text" id="contactPhone1" name="contactPhone1" />
      {#if $errors.contactPhone1}
        <Helper class="mt-2" color="red">
          {$errors.contactPhone1}
        </Helper>
      {/if}
    </div>
    <div class="w-full">
      <Label for="contactEmail1" class="mb-2 mt-2">{$translations.contact1EmailTitle}</Label>
      <Input type="text" id="contactEmail1" name="contactEmail1" />
      {#if $errors.contactEmail1}
        <Helper class="mt-2" color="red">
          {$errors.contactEmail1}
        </Helper>
      {/if}
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-3">
    <div class="w-full">
      <Label for="contactName2" class="mb-2 mt-2">{$translations.contact2NameTitle}</Label>
      <Input type="text" id="contactName2" name="contactName2" />
      {#if $errors.contactName2}
        <Helper class="mt-2" color="red">
          {$errors.contactName2}
        </Helper>
      {/if}
    </div>
    <div class="w-full">
      <Label for="contactPhone2" class="mb-2 mt-2">{$translations.contact2PhoneTitle}</Label>
      <Input type="text" id="contactPhone2" name="contactPhone2" />
      {#if $errors.contactPhone2}
        <Helper class="mt-2" color="red">
          {$errors.contactPhone2}
        </Helper>
      {/if}
    </div>
    <div class="w-full">
      <Label for="contactEmail2" class="mb-2 mt-2">{$translations.contact2EmailTitle}</Label>
      <Input type="text" id="contactEmail2" name="contactEmail2" />
      {#if $errors.contactEmail2}
        <Helper class="mt-2" color="red">
          {$errors.contactEmail2}
        </Helper>
      {/if}
    </div>
  </div>
  {#each globalSettings.customFields as { name, displayName }}
    <div class="grid gap-4 md:grid-cols-2">
      <div class="md:col-span-2">
        <Label for={name} class="mb-2 mt-2">{displayName}</Label>
        <Input type="text" id={name} name={$data.customFields[name]} bind:value={$data.customFields[name]} />
        {#if $errors.customFields?.[name]}
          <Helper class="mt-2" color="red">
            {$errors.customFields[name]}
          </Helper>
        {/if}
      </div>
    </div>
  {/each}
{/snippet}

{#snippet changesTabContent()}
  {#if changesLoaded && changes.length > 0}
    <div class="space-y-3">
      {#each changes as { dateString, details, userName }}
        <div class="rounded-lg bg-gray-100 p-3 shadow-sm dark:bg-gray-800">
          <div class="mb-0 flex items-center justify-between">
            <h6 class="text-lg font-semibold text-gray-900 dark:text-white">
              {dateString}
              {$translations.by}
              {userName}
            </h6>
          </div>
          {#if details && details.length > 0}
            <ul class="mt-1 space-y-1">
              {#each details as detail}
                <li class="text-sm text-gray-600 dark:text-gray-400">
                  {detail.trim()}
                </li>
              {/each}
            </ul>
          {/if}
        </div>
      {/each}
    </div>
  {/if}
{/snippet}

<form use:form>
  <BasicTabs changeActiveTab={handleTabChange} {tabs} {errorTabs} tabsSnippets={[basicTabContent, locationTabContent, otherTabContent, changesTabContent]} />
  <Hr hrClass="my-8" />
  <div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
      {#if geocodingError}
        <Helper class="mb-4 mt-2 pb-2 text-lg" color="red">
          {geocodingError}
        </Helper>
      {/if}
      {#if hasBasicErrors($errors) || hasLocationErrors($errors) || hasOtherErrors($errors)}
        <Helper class="mb-4 mt-2 pb-2 text-lg" color="red">
          {$translations.meetingErrorsSomewhere + ' ' + errorTabs.join(', ')}
        </Helper>
      {/if}
      <Button type="submit" class="w-full" disabled={!$isDirty} onclick={disableButtonHack}>
        {#if selectedMeeting}
          {$translations.applyChangesTitle}
        {:else}
          {$translations.addMeeting}
        {/if}
      </Button>
    </div>
  </div>
</form>
<MeetingDeleteModal bind:showDeleteModal {meetingToDelete} {onDeleted} />
