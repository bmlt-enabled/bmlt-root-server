<script lang="ts">
  import { validator } from '@felte/validator-yup';
  import { createForm } from 'felte';
  import { Badge, Button, Helper, Input, Label, MultiSelect, Select, Textarea } from 'flowbite-svelte';
  import { createEventDispatcher } from 'svelte';
  import * as yup from 'yup';

  import { spinner } from '../stores/spinner';
  import RootServerApi from '../lib/RootServerApi';
  import { formIsDirty } from '../lib/utils';
  import type { ServiceBody, ServiceBodyCreate, User } from 'bmlt-root-server-client';
  import { translations } from '../stores/localization';
  import { authenticatedUser } from '../stores/apiCredentials';

  export let selectedServiceBody: ServiceBody | null;
  export let serviceBodies: ServiceBody[];
  export let users: User[];

  const dispatch = createEventDispatcher<{ saved: { serviceBody: ServiceBody } }>();
  const parentIdItems = [
    ...[{ value: '-1', name: $translations.serviceBodiesNoParent ?? '' }],
    ...serviceBodies
      .filter((sb) => selectedServiceBody?.id !== sb.id)
      .map((sb) => ({ value: sb.id.toString(), name: sb.name }))
      .sort((a, b) => a.name.localeCompare(b.name))
  ];
  const userIdToUser = Object.fromEntries(users.map((u) => [u.id, u]));
  const userItems = users
    .filter((u) => {
      // We hide observer users, because they simply aren't allowed to edit meetings.
      // If an observer user is somehow already selected as an admin or meeting editor,
      // we allow it to be displayed in the list to give the user a chance to remove it.
      if (u.type !== 'observer') {
        return true;
      }
      if (selectedServiceBody?.adminUserId === u.id) {
        return true;
      }
      if ((selectedServiceBody?.assignedUserIds ?? []).includes(u.id)) {
        return true;
      }
      return false;
    })
    .map((u) => ({ value: u.id, name: u.displayName }))
    .sort((a, b) => a.name.localeCompare(b.name));

  const adminUserItems = userItems.map((u) => {
    const isDeactivated = userIdToUser[u.value].type === 'deactivated';
    return {
      value: u.value,
      name: isDeactivated ? `[${$translations.deactivatedTitle?.toUpperCase()}] ${u.name}` : u.name
    };
  });
  const SB_TYPE_AREA = 'AS';
  const typeItems = [
    { value: 'GR', name: 'Group' },
    { value: 'CO', name: 'Co-Op' },
    { value: 'GS', name: 'Group Service Unit' },
    { value: 'LS', name: 'Local Service Unit' },
    { value: SB_TYPE_AREA, name: 'Area Service Committee' },
    { value: 'MA', name: 'Metro Area' },
    { value: 'RS', name: 'Regional Service Conference' },
    { value: 'ZF', name: 'Zonal Forum' },
    { value: 'WS', name: 'World Service Conference' }
  ];
  const initialValues = {
    adminUserId: selectedServiceBody?.adminUserId ?? -1,
    type: selectedServiceBody?.type ?? SB_TYPE_AREA,
    parentId: selectedServiceBody?.parentId ?? -1,
    assignedUserIds: selectedServiceBody?.assignedUserIds ?? [],
    name: selectedServiceBody?.name ?? '',
    email: selectedServiceBody?.email ?? '',
    description: selectedServiceBody?.description ?? '',
    url: selectedServiceBody?.url ?? '',
    helpline: selectedServiceBody?.helpline ?? '',
    worldId: selectedServiceBody?.worldId ?? ''
  };
  let assignedUserIdsSelected = selectedServiceBody?.assignedUserIds ?? [];
  let savedServiceBody: ServiceBody;

  const { data, errors, form, isDirty, setData } = createForm({
    initialValues: initialValues,
    onSubmit: async (values) => {
      spinner.show();
      const serviceBody: ServiceBodyCreate = {
        ...values,
        // the api expects those with no parent to be null
        ...{ parentId: values.parentId !== -1 ? values.parentId : null }
      };
      if (selectedServiceBody) {
        await RootServerApi.updateServiceBody(selectedServiceBody.id, serviceBody);
        savedServiceBody = await RootServerApi.getServiceBody(selectedServiceBody.id);
      } else {
        savedServiceBody = await RootServerApi.createServiceBody(serviceBody);
      }
    },
    onError: async (error) => {
      console.log(error);
      await RootServerApi.handleErrors(error as Error, {
        handleValidationError: (error) => {
          errors.set({
            adminUserId: (error?.errors?.adminUserId ?? []).join(' '),
            type: (error?.errors?.type ?? []).join(' '),
            parentId: (error?.errors?.parentId ?? []).join(' '),
            assignedUserIds: (error?.errors?.assignedUserIds ?? []).join(' '),
            name: (error?.errors?.name ?? []).join(' '),
            email: (error?.errors?.email ?? []).join(' '),
            description: (error?.errors?.description ?? []).join(' '),
            url: (error?.errors?.url ?? []).join(' '),
            helpline: (error?.errors?.helpline ?? []).join(' '),
            worldId: (error?.errors?.worldId ?? []).join(' ')
          });
        }
      });
      spinner.hide();
    },
    onSuccess: () => {
      spinner.hide();
      dispatch('saved', { serviceBody: savedServiceBody });
    },
    extend: validator({
      schema: yup.object({
        adminUserId: yup.number().required(),
        type: yup.string().required(),
        parentId: yup.number().required(),
        assignedUserIds: yup.array().of(yup.number()),
        name: yup
          .string()
          .transform((v) => v.trim())
          .max(255)
          .required(),
        email: yup.string().email().max(255),
        description: yup.string().transform((v) => v.trim()),
        url: yup
          .string()
          .url()
          .transform((v) => v.trim())
          .max(255),
        helpline: yup
          .string()
          .transform((v) => v.trim())
          .max(255),
        worldId: yup
          .string()
          .transform((v) => v.trim())
          .max(30)
      }),
      castValues: true
    })
  });

  function badgeColor(id: string) {
    if (userIdToUser[id].type === 'deactivated') {
      return 'red';
    } else if (userIdToUser[id].type === 'observer') {
      return 'yellow';
    } else {
      return 'green';
    }
  }
  // This hack is required until https://github.com/themesberg/flowbite-svelte/issues/1395 is fixed.
  function disableButtonHack(event: MouseEvent) {
    if (!$isDirty) {
      event.preventDefault();
    }
  }

  $: setData('assignedUserIds', assignedUserIdsSelected);
  $: isDirty.set(formIsDirty(initialValues, $data));
</script>

<form use:form>
  <div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
      <Label for="name" class="mb-2">{$translations.nameTitle}</Label>
      <Input type="text" id="name" name="name" required disabled={$authenticatedUser?.type !== 'admin'} />
      <Helper class="mt-2" color="red">
        {#if $errors.name}
          {$errors.name}
        {/if}
      </Helper>
    </div>
    <div class="md:col-span-2 {$authenticatedUser?.type !== 'admin' ? 'hidden' : ''}">
      <Label for="type" class="mb-2">{$translations.adminTitle}</Label>
      <Select id="type" items={adminUserItems} name="adminUserId" class="dark:bg-gray-600" disabled={$authenticatedUser?.type !== 'admin'} />
      <Helper class="mt-2" color="red">
        {#if $errors.adminUserId}
          {$errors.adminUserId}
        {/if}
      </Helper>
    </div>
    <div class={$authenticatedUser?.type !== 'admin' ? 'hidden' : ''}>
      <Label for="type" class="mb-2">{$translations.serviceBodyTypeTitle}</Label>
      <Select id="type" items={typeItems} name="type" class="dark:bg-gray-600" disabled={$authenticatedUser?.type !== 'admin'} />
      <Helper class="mt-2" color="red">
        {#if $errors.type}
          {$errors.type}
        {/if}
      </Helper>
    </div>
    <div class={$authenticatedUser?.type !== 'admin' ? 'hidden' : ''}>
      <Label for="parentId" class="mb-2">{$translations.parentIdTitle}</Label>
      <Select id="parentId" items={parentIdItems} name="parentId" class="dark:bg-gray-600" disabled={$authenticatedUser?.type !== 'admin'} />
      <Helper class="mt-2" color="red">
        {#if $errors.parentId}
          {$errors.parentId}
        {/if}
      </Helper>
    </div>
    <div class="md:col-span-2">
      <Label for="assignedUserIds" class="mb-2">{$translations.meetingListEditorsTitle}</Label>
      <MultiSelect id="assignedUserIds" items={userItems} name="assignedUserIds" class="bg-gray-50 dark:bg-gray-600" bind:value={assignedUserIdsSelected} let:item let:clear>
        <Badge rounded color={badgeColor(item.value)} dismissable params={{ duration: 100 }} on:close={clear}>
          {item.name}
        </Badge>
      </MultiSelect>
      <Helper class="mt-2" color="red">
        <!-- For some reason yup fills the errors store with empty objects for this array. The === 'string' ensures only server side errors will display. -->
        {#if $errors.assignedUserIds && typeof $errors.assignedUserIds[0] === 'string'}
          {$errors.assignedUserIds}
        {/if}
      </Helper>
    </div>
    <div class="md:col-span-2">
      <Label for="email" class="mb-2">{$translations.emailTitle}</Label>
      <Input type="email" id="email" name="email" />
      <Helper class="mt-2" color="red">
        {#if $errors.email}
          {$errors.email}
        {/if}
      </Helper>
    </div>
    <div class="md:col-span-2">
      <Label for="description" class="mb-2">{$translations.descriptionTitle}</Label>
      <Textarea id="description" name="description" rows="4" />
      <Helper class="mt-2" color="red">
        {#if $errors.description}
          {$errors.description}
        {/if}
      </Helper>
    </div>
    <div class="md:col-span-2">
      <Label for="url" class="mb-2">{$translations.websiteUrlTitle}</Label>
      <Input type="text" id="url" name="url" />
      <Helper class="mt-2" color="red">
        {#if $errors.url}
          {$errors.url}
        {/if}
      </Helper>
    </div>
    <div class="md:col-span-2">
      <Label for="helpline" class="mb-2">{$translations.helplineTitle}</Label>
      <Input type="text" id="helpline" name="helpline" />
      <Helper class="mt-2" color="red">
        {#if $errors.helpline}
          {$errors.helpline}
        {/if}
      </Helper>
    </div>
    <div class="md:col-span-2">
      <Label for="worldId" class="mb-2">{$translations.worldIdTitle}</Label>
      <Input type="text" id="helpline" name="worldId" />
      <Helper class="mt-2" color="red">
        {#if $errors.worldId}
          {$errors.worldId}
        {/if}
      </Helper>
    </div>
    <div class="md:col-span-2">
      <Button type="submit" class="w-full" disabled={!$isDirty} on:click={disableButtonHack}>
        {#if selectedServiceBody}
          {$translations.applyChangesTitle}
        {:else}
          {$translations.addServiceBody}
        {/if}
      </Button>
    </div>
  </div>
</form>
