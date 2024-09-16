<script lang="ts">
  import { validator } from '@felte/validator-yup';
  import { createForm } from 'felte';
  import { Button, Checkbox, Hr, Label, Input, Helper, Select, MultiSelect, Badge } from 'flowbite-svelte';
  import { createEventDispatcher } from 'svelte';
  import * as yup from 'yup';
  import { Loader } from '@googlemaps/js-api-loader';
  import { writable } from 'svelte/store';

  const showMap = writable(false);
  // svelte-hack' -- import hacked to get onMount to work correctly for unit tests
  import MapAccordion from './MapAccordion.svelte';
  import { onMount } from 'svelte/internal';
  import { spinner } from '../stores/spinner';
  import RootServerApi from '../lib/RootServerApi';
  import { formIsDirty } from '../lib/utils';
  import type { Format, Meeting, MeetingPartialUpdate, ServiceBody } from 'bmlt-root-server-client';
  import { translations } from '../stores/localization';
  import MeetingDeleteModal from './MeetingDeleteModal.svelte';
  import { TrashBinOutline } from 'flowbite-svelte-icons';

  export let selectedMeeting: Meeting | null;
  export let formats: Format[];
  export let serviceBodies: ServiceBody[];

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

  let map: google.maps.Map;
  let mapElement: HTMLElement;
  let marker: google.maps.marker.AdvancedMarkerElement | null;
  let geocodingError: string | null = null;
  let isPublishedChecked = true;
  let showDeleteModal = false;
  let deleteMeeting: Meeting;
  const weekdayChoices = $translations.daysOfWeek.map((day, index) => ({
    value: index,
    name: day
  }));
  const venueTypeItems = [
    { value: VENUE_TYPE_IN_PERSON, name: 'In-Person' },
    { value: VENUE_TYPE_VIRTUAL, name: 'Virtual' },
    { value: VENUE_TYPE_HYBRID, name: 'Hybrid' }
  ];
  const dispatch = createEventDispatcher<{
    saved: { meeting: Meeting };
    deleted: { meetingId: number };
  }>();

  const defaultLatLng = { lat: Number(globalSettings.centerLatitude ?? -79.793701171875), lng: Number(globalSettings.centerLongitude ?? 36.065752051707) };
  const initialValues = {
    serviceBodyId: selectedMeeting?.serviceBodyId ?? -1,
    formatIds: selectedMeeting?.formatIds ?? [],
    venueType: selectedMeeting?.venueType ?? VENUE_TYPE_IN_PERSON,
    temporarilyVirtual: selectedMeeting?.temporarilyVirtual ?? false,
    day: selectedMeeting?.day ?? 0,
    startTime: selectedMeeting?.startTime ?? '12:00',
    duration: selectedMeeting?.duration ?? '01:00',
    timeZone: selectedMeeting?.timeZone ?? '',
    latitude: selectedMeeting?.latitude ?? defaultLatLng.lat,
    longitude: selectedMeeting?.longitude ?? defaultLatLng.lng,
    published: selectedMeeting?.published ?? false,
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
    trainLine: selectedMeeting?.trainLine ?? '',
    comments: selectedMeeting?.comments ?? ''
  };
  let latitude = initialValues.latitude;
  let longitude = initialValues.longitude;
  let manualDrag = false;
  let formatIdsSelected = initialValues.formatIds;
  let savedMeeting: Meeting;

  const { data, errors, form, setData, isDirty } = createForm({
    initialValues: initialValues,
    onSubmit: async (values) => {
      spinner.show();
      if (globalSettings.autoGeocodingEnabled && !manualDrag) {
        const geocodeResult = await geocode(values);
        if (typeof geocodeResult === 'string') {
          geocodingError = geocodeResult;
          spinner.hide();
          return;
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
            trainLine: (error?.errors?.train_line ?? []).join(' '),
            comments: (error?.errors?.comments ?? []).join(' ')
          });
        }
      });
      spinner.hide();
    },
    onSuccess: () => {
      spinner.hide();
      dispatch('saved', { meeting: savedMeeting });
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
        startTime: yup.string().required(), // TODO: Validation
        duration: yup.string().required(), // TODO: Validation
        timeZone: yup.string().max(40),
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
        locationStreet: yup.string().transform((v) => v.trim()),
        locationNeighborhood: yup.string().transform((v) => v.trim()),
        locationCitySubsection: yup.string().transform((v) => v.trim()),
        locationMunicipality: yup.string().transform((v) => v.trim()),
        locationSubProvince: yup.string().transform((v) => v.trim()),
        locationProvince: yup.string().transform((v) => v.trim()),
        locationPostalCode1: yup.string().transform((v) => v.trim()),
        locationNation: yup.string().transform((v) => v.trim()),
        phoneMeetingNumber: yup.string().transform((v) => v.trim()),
        virtualMeetingLink: yup.string().transform((v) => v.trim()),
        virtualMeetingAdditionalInfo: yup.string().transform((v) => v.trim()),
        contactName1: yup.string().transform((v) => v.trim()),
        contactName2: yup.string().transform((v) => v.trim()),
        contactPhone1: yup.string().transform((v) => v.trim()),
        contactPhone2: yup.string().transform((v) => v.trim()),
        contactEmail1: yup.string().transform((v) => v.trim()),
        contactEmail2: yup.string().transform((v) => v.trim()),
        busLines: yup.string().transform((v) => v.trim()),
        trainLine: yup.string().transform((v) => v.trim()),
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
    deleteMeeting = meeting;
    showDeleteModal = true;
  }

  function onDeleted(event: CustomEvent<{ meetingId: number }>) {
    dispatch('deleted', { meetingId: event.detail.meetingId });
    showDeleteModal = false;
  }

  // This hack is required until https://github.com/themesberg/flowbite-svelte/issues/1395 is fixed.
  function disableButtonHack(event: MouseEvent) {
    if (!$isDirty) {
      event.preventDefault();
    }
  }

  function geocode(meeting: MeetingPartialUpdate): Promise<{ lat: number; lng: number; county: string; zipCode: string } | null | string> {
    return new Promise((resolve) => {
      if (!meeting.locationNation) {
        meeting.locationNation = globalSettings.regionBias;
      }

      const address = [
        meeting.locationStreet,
        meeting.locationCitySubsection,
        meeting.locationMunicipality,
        meeting.locationProvince,
        meeting.locationSubProvince,
        meeting.locationPostalCode1,
        meeting.locationNation
      ]
        .filter(Boolean)
        .join(', ');

      const geocoder = new google.maps.Geocoder();
      let county = '';
      let zipCode = '';

      geocoder.geocode({ address: address }, (results, status) => {
        if (status === google.maps.GeocoderStatus.OK && results) {
          const location = results[0].geometry.location;
          for (let i = 0; i < results[0].address_components.length; i++) {
            const component = results[0].address_components[i];
            if (component.types.includes('postal_code')) {
              zipCode = component.long_name;
            }
            if (component.types.includes('administrative_area_level_2')) {
              county = component.long_name;
              if (county.endsWith(' County')) {
                county = county.substring(0, county.length - 7);
              }
            }
          }
          resolve({
            lat: location.lat(),
            lng: location.lng(),
            county: county || '',
            zipCode: zipCode || ''
          });
        } else {
          console.error('Geocoding failed:', status);
          resolve(`Geocoding failed: ${status}`);
        }
      });
    });
  }

  function createMarker(mapInstance: google.maps.Map, position: google.maps.LatLngLiteral): void {
    const naMarkerImage = document.createElement('img');
    naMarkerImage.src = 'images/NAMarkerR.png';
    marker = new google.maps.marker.AdvancedMarkerElement({
      position: position,
      map: mapInstance,
      gmpDraggable: true,
      content: naMarkerImage
    });

    marker.addListener('dragend', () => {
      if (marker && marker.position) {
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

  onMount(async () => {
    const loader = new Loader({
      apiKey: globalSettings.googleApikey,
      version: 'beta',
      libraries: ['places', 'marker', 'geocoding']
    });

    const { Map } = await loader.importLibrary('maps');

    mapElement = document.getElementById('locationMap') as HTMLElement;

    if (mapElement) {
      map = new Map(mapElement, {
        center: defaultLatLng,
        zoom: Number(globalSettings.centerZoom ?? 15),
        draggableCursor: 'crosshair',
        mapId: 'bmlt'
      });

      showMap.subscribe((value) => {
        mapElement.style.display = value ? 'block' : 'none';
        if (value && map) {
          map.setCenter({ lat: latitude, lng: longitude });
          createMarker(map, { lat: latitude, lng: longitude });
        }
      });
    }
  });

  $: {
    if (selectedMeeting) {
      showMap.set(true);
      manualDrag = false;
    }
  }

  $: setData('formatIds', formatIdsSelected);
  $: isDirty.set(formIsDirty(initialValues, $data));
</script>

<form use:form>
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
          on:click={(e) => selectedMeeting && handleDelete(e, selectedMeeting)}
          class="text-red-600 dark:text-red-500"
          aria-label={$translations.deleteMeeting + ' ' + (selectedMeeting?.name ?? '')}
        >
          <TrashBinOutline title={{ id: 'deleteMeeting', title: $translations.deleteMeeting }} ariaLabel={$translations.deleteMeeting} />
          <span class="sr-only">{$translations.deleteMeeting}</span>
        </Button>
      </div>
    {/if}
  </div>
  <div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
      <Label for="name" class="mb-2">{$translations.nameTitle}</Label>
      <Input type="text" id="name" name="name" required />
      <Helper class="mt-2" color="red">
        {#if $errors.name}
          {$errors.name}
        {/if}
      </Helper>
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
      <Label for="timeZone" class="mb-2">{$translations.timeZoneTitle}</Label>
      <Input type="text" id="timeZone" name="timeZone" required />
      <Helper class="mt-2" color="red">
        {#if $errors.timeZone}
          {$errors.timeZone}
        {/if}
      </Helper>
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-3">
    <div class="w-full">
      <Label for="day" class="mb-2">{$translations.dayTitle}</Label>
      <Select id="day" items={weekdayChoices} name="day" class="dark:bg-gray-600" />
      <Helper class="mt-2" color="red">
        {#if $errors.day}
          {$errors.day}
        {/if}
      </Helper>
    </div>
    <div class="w-full">
      <Label for="startTime" class="mb-2">{$translations.startTimeTitle}</Label>
      <Input type="time" id="startTime" name="startTime" />
      <Helper class="mt-2" color="red">
        {#if $errors.startTime}
          {$errors.startTime}
        {/if}
      </Helper>
    </div>
    <div class="w-full">
      <Label for="duration" class="mb-2">{$translations.durationTitle}</Label>
      <Input type="text" id="duration" name="duration" />
      <Helper class="mt-2" color="red">
        {#if $errors.duration}
          {$errors.duration}
        {/if}
      </Helper>
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
      <Label for="serviceBodyId" class="mb-2">{$translations.serviceBodyTitle}</Label>
      <Select id="serviceBodyId" items={serviceBodyIdItems} name="serviceBodyId" class="dark:bg-gray-600" />
      <Helper class="mt-2" color="red">
        {#if $errors.serviceBodyId}
          {$errors.serviceBodyId}
        {/if}
      </Helper>
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-2">
    <div class="w-full">
      <Label for="email" class="mb-2">{$translations.emailTitle}</Label>
      <Input type="email" id="email" name="email" />
      <Helper class="mt-2" color="red">
        {#if $errors.email}
          {$errors.email}
        {/if}
      </Helper>
    </div>
    <div class="w-full">
      <Label for="worldId" class="mb-2">{$translations.worldIdTitle}</Label>
      <Input type="text" id="worldId" name="worldId" />
      <Helper class="mt-2" color="red">
        {#if $errors.worldId}
          {$errors.worldId}
        {/if}
      </Helper>
    </div>
  </div>
  <Hr classHr="my-8" />
  <div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
      <Label for="venueType" class="mb-2">{$translations.venueTypeTitle}</Label>
      <Select id="venueType" items={venueTypeItems} name="venueType" class="dark:bg-gray-600" />
      <Helper class="mt-2" color="red">
        {#if $errors.venueType}
          {$errors.venueType}
        {/if}
      </Helper>
    </div>
  </div>
  {#if selectedMeeting}
    <div class="grid gap-4 md:grid-cols-2">
      <div class="md:col-span-2">
        <MapAccordion title={$translations.locationMapTitle}>
          <div id="locationMap" bind:this={mapElement} />
        </MapAccordion>
      </div>
    </div>
  {/if}
  <div class="grid gap-4 md:grid-cols-2">
    <div class="w-full">
      <Label for="longitude" class="mb-2">{$translations.longitudeTitle}</Label>
      <Input type="text" id="longitude" name="longitude" bind:value={longitude} required />
      <Helper class="mt-2" color="red">
        {#if $errors.longitude}
          {$errors.longitude}
        {/if}
      </Helper>
    </div>
    <div class="w-full">
      <Label for="latitude" class="mb-2">{$translations.latitudeTitle}</Label>
      <Input type="text" id="latitude" name="latitude" bind:value={latitude} required />
      <Helper class="mt-2" color="red">
        {#if $errors.latitude}
          {$errors.latitude}
        {/if}
      </Helper>
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
      <Label for="locationText" class="mb-2">{$translations.locationTextTitle}</Label>
      <Input type="text" id="locationText" name="locationText" />
      <Helper class="mt-2" color="red">
        {#if $errors.locationText}
          {$errors.locationText}
        {/if}
      </Helper>
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
      <Label for="locationInfo" class="mb-2">{$translations.extraInfoTitle}</Label>
      <Input type="text" id="locationInfo" name="locationInfo" />
      <Helper class="mt-2" color="red">
        {#if $errors.locationInfo}
          {$errors.locationInfo}
        {/if}
      </Helper>
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
      <Label for="locationStreet" class="mb-2">{$translations.streetTitle}</Label>
      <Input type="text" id="locationStreet" name="locationStreet" />
      <Helper class="mt-2" color="red">
        {#if $errors.locationStreet}
          {$errors.locationStreet}
        {/if}
      </Helper>
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-2">
    <div class="w-full">
      <Label for="locationNeighborhood" class="mb-2">{$translations.neighborhoodTitle}</Label>
      <Input type="text" id="locationNeighborhood" name="locationNeighborhood" />
      <Helper class="mt-2" color="red">
        {#if $errors.locationNeighborhood}
          {$errors.locationNeighborhood}
        {/if}
      </Helper>
    </div>
    <div class="w-full">
      <Label for="locationCitySubsection" class="mb-2">{$translations.boroughTitle}</Label>
      <Input type="text" id="locationCitySubsection" name="locationCitySubsection" />
      <Helper class="mt-2" color="red">
        {#if $errors.locationCitySubsection}
          {$errors.locationCitySubsection}
        {/if}
      </Helper>
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-2">
    <div class="w-full">
      <Label for="locationMunicipality" class="mb-2">{$translations.cityTownTitle}</Label>
      <Input type="text" id="locationMunicipality" name="locationMunicipality" />
      <Helper class="mt-2" color="red">
        {#if $errors.locationMunicipality}
          {$errors.locationMunicipality}
        {/if}
      </Helper>
    </div>
    <div class="w-full">
      <Label for="locationSubProvince" class="mb-2">{$translations.countySubProvinceTitle}</Label>
      <Input type="text" id="locationSubProvince" name="locationSubProvince" />
      <Helper class="mt-2" color="red">
        {#if $errors.locationSubProvince}
          {$errors.locationSubProvince}
        {/if}
      </Helper>
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-3">
    <div class="w-full">
      <Label for="locationProvince" class="mb-2">{$translations.stateTitle}</Label>
      <Input type="text" id="locationProvince" name="locationProvince" />
      <Helper class="mt-2" color="red">
        {#if $errors.locationProvince}
          {$errors.locationProvince}
        {/if}
      </Helper>
    </div>
    <div class="w-full">
      <Label for="locationPostalCode1" class="mb-2">{$translations.zipCodeTitle}</Label>
      <Input type="text" id="locationPostalCode1" name="locationPostalCode1" />
      <Helper class="mt-2" color="red">
        {#if $errors.locationPostalCode1}
          {$errors.locationPostalCode1}
        {/if}
      </Helper>
    </div>
    <div class="w-full">
      <Label for="locationNation" class="mb-2">{$translations.nationTitle}</Label>
      <Input type="text" id="locationNation" name="locationNation" />
      <Helper class="mt-2" color="red">
        {#if $errors.locationNation}
          {$errors.locationNation}
        {/if}
      </Helper>
    </div>
  </div>
  <Hr classHr="my-8" />
  <div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
      <Label for="phoneMeetingNumber" class="mb-2">{$translations.phoneMeetingTitle}</Label>
      <Input type="text" id="phoneMeetingNumber" name="phoneMeetingNumber" />
      <Helper class="mt-2" color="red">
        {#if $errors.phoneMeetingNumber}
          {$errors.phoneMeetingNumber}
        {/if}
      </Helper>
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
      <Label for="virtualMeetingLink" class="mb-2">{$translations.virtualMeetingTitle}</Label>
      <Input type="text" id="virtualMeetingLink" name="virtualMeetingLink" />
      <Helper class="mt-2" color="red">
        {#if $errors.virtualMeetingLink}
          {$errors.virtualMeetingLink}
        {/if}
      </Helper>
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
      <Label for="virtualMeetingAdditionalInfo" class="mb-2">{$translations.virtualMeetingAdditionalInfoTitle}</Label>
      <Input type="text" id="virtualMeetingAdditionalInfo" name="virtualMeetingAdditionalInfo" />
      <Helper class="mt-2" color="red">
        {#if $errors.virtualMeetingAdditionalInfo}
          {$errors.virtualMeetingAdditionalInfo}
        {/if}
      </Helper>
    </div>
  </div>
  <Hr classHr="my-8" />
  <div class="md:col-span-2">
    <Label for="formatIds" class="mb-2">{$translations.formatsTitle}</Label>
    <MultiSelect id="formatIds" items={formatItems} name="formatIds" class="bg-gray-50 dark:bg-gray-600" bind:value={formatIdsSelected} let:item let:clear>
      <Badge rounded color={badgeColor(item.value)} dismissable params={{ duration: 100 }} on:close={clear}>
        {item.name}
      </Badge>
    </MultiSelect>
    <Helper class="mt-2" color="red">
      <!-- For some reason yup fills the errors store with empty objects for this array. The === 'string' ensures only server side errors will display. -->
      {#if $errors.formatIds && typeof $errors.formatIds[0] === 'string'}
        {$errors.formatIds}
      {/if}
    </Helper>
  </div>
  <Hr classHr="my-8" />
  <div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
      <Label for="comments" class="mb-2">{$translations.commentsTitle}</Label>
      <Input type="text" id="comments" name="comments" />
      <Helper class="mt-2" color="red">
        {#if $errors.comments}
          {$errors.comments}
        {/if}
      </Helper>
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-2">
    <div class="w-full">
      <Label for="busLines" class="mb-2">{$translations.busLineTitle}</Label>
      <Input type="text" id="busLines" name="busLines" />
      <Helper class="mt-2" color="red">
        {#if $errors.busLines}
          {$errors.busLines}
        {/if}
      </Helper>
    </div>
    <div class="w-full">
      <Label for="trainLine" class="mb-2">{$translations.trainLineTitle}</Label>
      <Input type="text" id="trainLine" name="trainLine" />
      <Helper class="mt-2" color="red">
        {#if $errors.trainLine}
          {$errors.trainLine}
        {/if}
      </Helper>
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-3">
    <div class="w-full">
      <Label for="contactName1" class="mb-2">{$translations.contact1NameTitle}</Label>
      <Input type="text" id="contactName1" name="contactName1" />
      <Helper class="mt-2" color="red">
        {#if $errors.contactName1}
          {$errors.contactName1}
        {/if}
      </Helper>
    </div>
    <div class="w-full">
      <Label for="contactPhone1" class="mb-2">{$translations.contact1PhoneTitle}</Label>
      <Input type="text" id="contactPhone1" name="contactPhone1" />
      <Helper class="mt-2" color="red">
        {#if $errors.contactPhone1}
          {$errors.contactPhone1}
        {/if}
      </Helper>
    </div>
    <div class="w-full">
      <Label for="contactEmail1" class="mb-2">{$translations.contact1EmailTitle}</Label>
      <Input type="text" id="contactEmail1" name="contactEmail1" />
      <Helper class="mt-2" color="red">
        {#if $errors.contactEmail1}
          {$errors.contactEmail1}
        {/if}
      </Helper>
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-3">
    <div class="w-full">
      <Label for="contactName2" class="mb-2">{$translations.contact2NameTitle}</Label>
      <Input type="text" id="contactName2" name="contactName2" />
      <Helper class="mt-2" color="red">
        {#if $errors.contactName2}
          {$errors.contactName2}
        {/if}
      </Helper>
    </div>
    <div class="w-full">
      <Label for="contactPhone2" class="mb-2">{$translations.contact2PhoneTitle}</Label>
      <Input type="text" id="contactPhone2" name="contactPhone2" />
      <Helper class="mt-2" color="red">
        {#if $errors.contactPhone2}
          {$errors.contactPhone2}
        {/if}
      </Helper>
    </div>
    <div class="w-full">
      <Label for="contactEmail2" class="mb-2">{$translations.contact2EmailTitle}</Label>
      <Input type="text" id="contactEmail2" name="contactEmail" />
      <Helper class="mt-2" color="red">
        {#if $errors.contactEmail2}
          {$errors.contactEmail2}
        {/if}
      </Helper>
    </div>
  </div>
  <Hr classHr="my-8" />
  <div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
      {#if geocodingError}
        <Helper class="mt-2" color="red">
          {geocodingError}
        </Helper>
      {/if}
      <Button type="submit" class="w-full" disabled={!$isDirty} on:click={disableButtonHack}>
        {#if selectedMeeting}
          {$translations.applyChangesTitle}
        {:else}
          {$translations.addMeeting}
        {/if}
      </Button>
    </div>
  </div>
</form>
<MeetingDeleteModal bind:showDeleteModal {deleteMeeting} on:deleted={onDeleted} />
