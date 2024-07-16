<script lang="ts">
  import { validator } from '@felte/validator-yup';
  import { createForm } from 'felte';
  import { Button, Helper, Input, Label, MultiSelect, Select, Textarea } from 'flowbite-svelte';
  import { createEventDispatcher } from 'svelte';
  import * as yup from 'yup';

  import { spinner } from '../stores/spinner';
  import RootServerApi from '../lib/RootServerApi';
  import type { ServiceBody } from 'bmlt-root-server-client';
  import { translations } from '../stores/localization';
  import { authenticatedUser } from '../stores/apiCredentials';
  import { usersData } from '../stores/users';

  export let selectedServiceBody: ServiceBody | null;
  export let serviceBodies: ServiceBody[];

  const dispatch = createEventDispatcher<{ saved: { serviceBody: ServiceBody } }>();
  const userOwnerItems = serviceBodies
    .filter((u) => selectedServiceBody?.id !== u.id)
    .map((u) => ({ value: u.id.toString(), name: u.name }))
    .sort((a, b) => a.name.localeCompare(b.name));
  const userEditorItems = $usersData
    .filter((u) => selectedServiceBody?.id !== u.id)
    .map((u) => ({ value: u.id.toString(), name: u.displayName }))
    .sort((a, b) => a.name.localeCompare(b.name));

  const SB_TYPE_GROUP = 'GR';
  const SB_TYPE_COOP = 'CO';
  const SB_TYPE_GSU = 'GS';
  const SB_TYPE_LSU = 'LS';
  const SB_TYPE_AREA = 'AS';
  const SB_TYPE_METRO = 'MA';
  const SB_TYPE_REGION = 'RS';
  const SB_TYPE_ZONE = 'ZF';
  const SB_TYPE_WORLD = 'WS';
  const serviceBodyTypeItems = [
    { value: SB_TYPE_GROUP, name: 'Group' },
    { value: SB_TYPE_COOP, name: 'Co-Op' },
    { value: SB_TYPE_GSU, name: 'Group Service Unit' },
    { value: SB_TYPE_LSU, name: 'Local Service Unit' },
    { value: SB_TYPE_AREA, name: 'Area Service Committee' },
    { value: SB_TYPE_METRO, name: 'Metro Area' },
    { value: SB_TYPE_REGION, name: 'Regional Service Conference' },
    { value: SB_TYPE_ZONE, name: 'Zonal Forum' },
    { value: SB_TYPE_WORLD, name: 'World Service Conference' }
  ];
  let savedServiceBody: ServiceBody;
  let assignedUserIdsSelected: number[] = [];

  const { form, errors, setInitialValues, reset } = createForm({
    initialValues: {
      type: SB_TYPE_AREA,
      parentId: $authenticatedUser?.id,
      adminUserId: -1,
      email: '',
      name: '',
      url: '',
      helpline: '',
      description: '',
      worldId: '',
      assignedUserIds: [] as number[]
    },
    onSubmit: async (values) => {
      spinner.show();
      if (selectedServiceBody) {
        await RootServerApi.updateServiceBody(selectedServiceBody.id, values);
        savedServiceBody = await RootServerApi.getServiceBody(selectedServiceBody.id);
      } else {
        savedServiceBody = await RootServerApi.createServiceBody(values);
      }
    },
    onError: async (error) => {
      console.log(error);
      await RootServerApi.handleErrors(error as Error, {
        handleValidationError: (error) => {
          errors.set({
            type: (error?.errors?.type ?? []).join(' '),
            parentId: (error?.errors?.parentId ?? []).join(' '),
            adminUserId: (error?.errors?.adminUserId ?? []).join(' '),
            email: (error?.errors?.email ?? []).join(' '),
            name: (error?.errors?.name ?? []).join(' '),
            url: (error?.errors?.url ?? []).join(' '),
            helpline: (error?.errors?.helpline ?? []).join(' '),
            description: (error?.errors?.description ?? []).join(' '),
            worldId: (error?.errors?.worldId ?? []).join(' '),
            assignedUserIds: (error?.errors?.assignedUserIds ?? []).join(' ')
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
        type: yup.string().required(),
        parentId: yup
          .number()
          .transform((v) => parseInt(v))
          .required(),
        adminUserId: yup
          .number()
          .transform((v) => parseInt(v))
          .required(),
        email: yup.string().max(255).email(),
        name: yup
          .string()
          .transform((v) => v.trim())
          .max(255)
          .required(),
        url: yup
          .string()
          .transform((v) => v.trim())
          .max(255),
        helpline: yup
          .string()
          .transform((v) => v.trim())
          .max(255),
        worldId: yup
          .string()
          .transform((v) => v.trim())
          .max(255),
        description: yup.string(),
        assignedUserIds: yup.array().of(yup.number().transform((v) => parseInt(v)))
      }),
      castValues: true
    })
  });

  function populateForm() {
    // The only reason we use setInitialValues and reset here instead of setData is to make development
    // easier. It is super annoying that each time we save the file, hot module replacement causes the
    // values in the form fields to be replaced when the UsersForm is refreshed.
    setInitialValues({
      type: selectedServiceBody?.type ?? SB_TYPE_AREA,
      // TODO: Handle no parent, admin userId, assignedUserIds
      parentId: selectedServiceBody?.parentId ?? ($authenticatedUser?.type === 'admin' ? $authenticatedUser.id : -1),
      adminUserId: -1,
      email: selectedServiceBody?.email ?? '',
      name: selectedServiceBody?.name ?? '',
      url: selectedServiceBody?.url ?? '',
      helpline: selectedServiceBody?.helpline ?? '',
      description: selectedServiceBody?.description ?? '',
      worldId: selectedServiceBody?.worldId ?? '',
      assignedUserIds: selectedServiceBody?.assignedUserIds ?? []
    });
    reset();
  }

  $: if (selectedServiceBody) {
    populateForm();
  }
</script>

<form use:form>
  <div class="md:col-span-2">
    <div class={$authenticatedUser?.type !== 'admin' ? 'hidden' : ''}>
      <Label for="type" class="mb-2">{$translations.primaryAdminTitle}</Label>
      <!--        TODO: This should be list of users if server admin or just text with user name prob if not-->
      <Select id="type" items={userEditorItems} name="adminUserId" disabled={$authenticatedUser?.type !== 'admin'} />
      <Helper class="mt-2" color="red">
        {#if $errors.adminUserId}
          {$errors.adminUserId}
        {/if}
      </Helper>
    </div>
  </div>
  <div class="grid gap-4 md:grid-cols-2">
    <div class={$authenticatedUser?.type !== 'admin' ? 'hidden' : ''}>
      <Label for="type" class="mb-2">{$translations.serviceBodyTypeTitle}</Label>
      <Select id="type" items={serviceBodyTypeItems} name="type" disabled={$authenticatedUser?.type !== 'admin'} />
      <Helper class="mt-2" color="red">
        {#if $errors.type}
          {$errors.type}
        {/if}
      </Helper>
    </div>
    <div class={$authenticatedUser?.type !== 'admin' ? 'hidden' : ''}>
      <Label for="parentId" class="mb-2">{$translations.parentIdTitle}</Label>
      <!--            TODO: Handle No Parent, assignedUserIds-->
      <Select id="parentId" items={userOwnerItems} name="parentId" disabled={$authenticatedUser?.type !== 'admin'} />
      <Helper class="mt-2" color="red">
        {#if $errors.parentId}
          {$errors.parentId}
        {/if}
      </Helper>
    </div>
    <div class="md:col-span-2">
      <Label for="type" class="mb-2">{$translations.meetingListEditorsTitle}</Label>
      <!--        TODO: User selection, observers?-->
      <MultiSelect id="assignedUserIds" items={userEditorItems} bind:value={assignedUserIdsSelected} />
      <Helper class="mt-2" color="red">
        {#if $errors.assignedUserIds}
          {$errors.assignedUserIds}
        {/if}
      </Helper>
    </div>
    <div class="md:col-span-2">
      <Label for="name" class="mb-2">{$translations.nameTitle}</Label>
      <Input type="text" id="name" name="name" required />
      <Helper class="mt-2" color="red">
        {#if $errors.name}
          {$errors.name}
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
      <Button type="submit" class="w-full">
        {#if selectedServiceBody}
          {$translations.applyChangesTitle}
        {:else}
          {$translations.addUser}
        {/if}
      </Button>
    </div>
  </div>
</form>
