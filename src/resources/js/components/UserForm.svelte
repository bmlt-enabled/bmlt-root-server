<script lang="ts">
  import { validator } from '@felte/validator-yup';
  import { createForm } from 'felte';
  import { Button, Helper, Input, Label, Select } from 'flowbite-svelte';
  import * as yup from 'yup';

  import { spinner } from '../stores/spinner';
  import RootServerApi from '../lib/RootServerApi';
  import { formIsDirty } from '../lib/utils';
  import type { User } from 'bmlt-root-server-client';
  import { translations } from '../stores/localization';
  import { authenticatedUser } from '../stores/apiCredentials';

  interface Props {
    selectedUser: User | null;
    users: User[];
    onSaveSuccess?: (user: User) => void; // Callback function prop
  }

  let { selectedUser, users, onSaveSuccess }: Props = $props();

  const userOwnerItems = users
    .filter((u) => selectedUser?.id !== u.id)
    .map((u) => ({ value: u.id.toString(), name: u.displayName }))
    .sort((a, b) => a.name.localeCompare(b.name));
  const USER_TYPE_DEACTIVATED = 'deactivated';
  const USER_TYPE_OBSERVER = 'observer';
  const USER_TYPE_SERVICE_BODY_ADMIN = 'serviceBodyAdmin';
  const userTypeItems = [
    { value: USER_TYPE_DEACTIVATED, name: 'Deactivated' },
    { value: USER_TYPE_OBSERVER, name: 'Observer' },
    { value: USER_TYPE_SERVICE_BODY_ADMIN, name: 'Service Body Administrator' }
  ];
  const initialValues = {
    type: selectedUser?.type ?? USER_TYPE_SERVICE_BODY_ADMIN,
    ownerId: selectedUser?.ownerId ?? ($authenticatedUser?.type === 'admin' ? $authenticatedUser.id : -1),
    email: selectedUser?.email ?? '',
    displayName: selectedUser?.displayName ?? '',
    username: selectedUser?.username ?? '',
    password: '',
    description: selectedUser?.description ?? ''
  };
  let savedUser: User;

  const { data, errors, form, isDirty } = createForm({
    initialValues: initialValues,
    onSubmit: async (values) => {
      spinner.show();
      if (selectedUser) {
        await RootServerApi.updateUser(selectedUser.id, values);
        savedUser = await RootServerApi.getUser(selectedUser.id);
      } else {
        savedUser = await RootServerApi.createUser(values);
      }
    },
    onError: async (error) => {
      console.log(error);
      await RootServerApi.handleErrors(error as Error, {
        handleValidationError: (error) => {
          errors.set({
            type: (error?.errors?.type ?? []).join(' '),
            ownerId: (error?.errors?.ownerId ?? []).join(' '),
            email: (error?.errors?.email ?? []).join(' '),
            displayName: (error?.errors?.displayName ?? []).join(' '),
            username: (error?.errors?.username ?? []).join(' '),
            password: (error?.errors?.password ?? []).join(' '),
            description: (error?.errors?.description ?? []).join(' ')
          });
        }
      });
      spinner.hide();
    },
    onSuccess: () => {
      spinner.hide();
      onSaveSuccess?.(savedUser); // Call the callback function instead of dispatch
    },
    extend: validator({
      schema: yup.object({
        type: yup.string().required(),
        ownerId: yup
          .number()
          .transform((v) => parseInt(v))
          .required(),
        email: yup.string().max(255).email(),
        displayName: yup
          .string()
          .transform((v) => v.trim())
          .max(255)
          .required(),
        username: yup
          .string()
          .transform((v) => v.trim())
          .max(255)
          .required(),
        password: yup
          .string()
          .transform((v) => (v ? v : undefined))
          .test('validatePassword', 'password must be between 12 and 255 characters', (password) => {
            const isEditing = selectedUser !== null;
            if (!password) {
              return isEditing ? true : false;
            }

            if (password.length < 12) {
              return false;
            }

            if (password.length > 255) {
              return false;
            }

            return true;
          }),
        description: yup
          .string()
          .transform((v) => v.trim())
          .max(255)
      }),
      castValues: true
    })
  });

  // This hack is required until https://github.com/themesberg/flowbite-svelte/issues/1395 is fixed.
  function disableButtonHack(event: MouseEvent) {
    if (!$isDirty) {
      event.preventDefault();
    }
  }

  $effect(() => {
    isDirty.set(formIsDirty(initialValues, $data));
  });
</script>

<form use:form>
  <div class="grid gap-4 md:grid-cols-2">
    <div class={$authenticatedUser?.type !== 'admin' ? 'hidden' : ''}>
      <Label for="type" class="mb-2">{$translations.userTypeTitle}</Label>
      <Select id="type" items={userTypeItems} name="type" class="dark:bg-gray-600" disabled={$authenticatedUser?.type !== 'admin'} />
      <Helper class="mt-2" color="red">
        {#if $errors.type}
          {$errors.type}
        {/if}
      </Helper>
    </div>
    <div class={$authenticatedUser?.type !== 'admin' ? 'hidden' : ''}>
      <Label for="ownerId" class="mb-2">{$translations.ownedByTitle}</Label>
      <Select id="ownerId" items={userOwnerItems} name="ownerId" class="dark:bg-gray-600" disabled={$authenticatedUser?.type !== 'admin'} />
      <Helper class="mt-2" color="red">
        {#if $errors.ownerId}
          {$errors.ownerId}
        {/if}
      </Helper>
    </div>
    <div class="md:col-span-2">
      <Label for="displayName" class="mb-2">{$translations.nameTitle}</Label>
      <Input type="text" id="displayName" name="displayName" required />
      <Helper class="mt-2" color="red">
        {#if $errors.displayName}
          {$errors.displayName}
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
      <Input type="text" id="description" name="description" />
      <Helper class="mt-2" color="red">
        {#if $errors.description}
          {$errors.description}
        {/if}
      </Helper>
    </div>
    <div class="md:col-span-2">
      <Label for="username" class="mb-2">{$translations.usernameTitle}</Label>
      <Input type="text" id="username" name="username" required />
      <Helper class="mt-2" color="red">
        {#if $errors.username}
          {$errors.username}
        {/if}
      </Helper>
    </div>
    <div class="md:col-span-2">
      <Label for="password" class="mb-2">{$translations.passwordTitle}</Label>
      <Input type="password" id="password" name="password" required />
      <Helper class="mt-2" color="red">
        {#if $errors.password}
          {$errors.password}
        {/if}
      </Helper>
    </div>
    <div class="md:col-span-2">
      <Button type="submit" class="w-full" disabled={!$isDirty} on:click={disableButtonHack}>
        {#if selectedUser}
          {$translations.applyChangesTitle}
        {:else}
          {$translations.addUser}
        {/if}
      </Button>
    </div>
  </div>
</form>
