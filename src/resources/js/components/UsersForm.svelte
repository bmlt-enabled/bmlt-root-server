<script lang="ts">
  import { validator } from '@felte/validator-yup';
  import { createForm } from 'felte';
  import type { UserUpdate } from 'bmlt-root-server-client';
  import { Button, Helper, Input, Label, P, Select } from 'flowbite-svelte';
  import * as yup from 'yup';

  import { authenticatedUser } from '../stores/apiCredentials';
  import { spinner } from '../stores/spinner';
  import { translations } from '../stores/localization';
  import type { User } from 'bmlt-root-server-client';
  import RootServerApi from '../lib/RootServerApi';

  export let selectedUserId: number;
  export let usersById: Record<number, User> = {};
  export let userItems = [{ value: -1, name: '' }];

  const userTypeItems = [
    { value: 'deactivated', name: 'Deactivated' },
    { value: 'observer', name: 'Observer' },
    { value: 'serviceBodyAdmin', name: 'Service Body Administrator' }
  ];

  let errorMessage: string | undefined = '';
  let disableUserType = false;

  const { form, data, errors } = createForm({
    initialValues: {
      user: '',
      type: '',
      ownedBy: -1,
      email: '',
      displayName: '',
      username: '',
      password: '',
      description: ''
    },
    onSubmit: async (values) => {
      spinner.show();
      console.log(values);
      await RootServerApi.updateUser(selectedUserId, values as UserUpdate);
    },
    onError: async (error) => {
      console.log(error);
      await RootServerApi.handleErrors(error as Error, {
        handleAuthenticationError: () => {
          console.log(error);
          errorMessage = $translations.invalidUsernameOrPassword;
        },
        handleValidationError: (error) => {
          console.log(error);
          errors.set({
            username: (error?.errors?.username ?? []).join(' '),
            password: (error?.errors?.password ?? []).join(' ')
          });
        }
      });
      spinner.hide();
    },
    onSuccess: () => {
      // Success Modal ?
      spinner.hide();
    },
    extend: validator({
      schema: yup
        .object({
          user: yup.string().required('User is required'),
          type: yup.string(),
          ownedBy: yup.number(),
          email: yup.string().max(255, 'email cannot be longer than 255 characters').email('Invalid email'),
          displayName: yup.string().max(255, 'display name cannot be longer than 255 characters').required('Name is required'),
          username: yup.string().max(255, 'username cannot be longer than 255 characters').required('Username is required'),
          password: yup.string().min(12, 'password must be at least 12 characters long').max(255, 'password cannot be longer than 255 characters'),
          description: yup.string().max(255, 'description cannot be longer than 255 characters')
        })
        .test('type-validation', 'User type validation failed', function (value) {
          const { type, ownedBy } = value;
          if (type !== 'admin' && !ownedBy) {
            return new yup.ValidationError('Owner is required for non-admin users', null, 'ownedBy');
          }
          if (type !== 'admin' && !type) {
            return new yup.ValidationError('User Type is required for non-admin users', null, 'ownedBy');
          }
          return true;
        })
    })
  });

  function populateForm() {
    const user = usersById[selectedUserId];
    if (!user) {
      return;
    }
    $data.type = user.type;
    $data.ownedBy = user.ownerId ? parseInt(user.ownerId) : -1;
    $data.email = user.email;
    $data.displayName = user.displayName;
    $data.username = user.username;
    $data.description = user.description;
    $data.password = ''; // Clear password field for security reasons
    disableUserType = user.id === $authenticatedUser?.id && $authenticatedUser?.type === 'admin';
  }

  $: if (selectedUserId) {
    populateForm();
  }
</script>

<form use:form>
  <div class="mb-6 grid gap-6 md:grid-cols-2">
    <div>
      <Label for="user-type" class="mb-2">{$translations.userTypeTitle}</Label>
      <Select id="user-type" items={userTypeItems} name="type" bind:value={$data.type} disabled={disableUserType} />
      <Helper class="mt-2" color="red">
        {#if $errors.type}
          {$errors.type}
        {/if}
      </Helper>
    </div>
    <div>
      <Label for="owned-by" class="mb-2">{$translations.ownedByTitle}</Label>
      <Select id="owned-by" items={userItems} name="ownedBy" bind:value={$data.ownedBy} disabled={disableUserType} />
      <Helper class="mt-2" color="red">
        {#if $errors.ownedBy}
          {$errors.ownedBy}
        {/if}
      </Helper>
    </div>
  </div>
  <div class="mb-6">
    <Label for="email" class="mb-2">{$translations.emailTitle}</Label>
    <Input type="email" id="email" name="email" bind:value={$data.email} />
    <Helper class="mt-2" color="red">
      {#if $errors.email}
        {$errors.email}
      {/if}
    </Helper>
  </div>
  <div class="mb-6">
    <Label for="name" class="mb-2">{$translations.nameTitle}</Label>
    <Input type="text" id="name" name="name" bind:value={$data.displayName} required />
    <Helper class="mt-2" color="red">
      {#if $errors.displayName}
        {$errors.displayName}
      {/if}
    </Helper>
  </div>
  <div class="mb-6">
    <Label for="description" class="mb-2">{$translations.descriptionTitle}</Label>
    <Input type="text" id="description" name="description" bind:value={$data.description} />
    <Helper class="mt-2" color="red">
      {#if $errors.description}
        {$errors.description}
      {/if}
    </Helper>
  </div>
  <div class="mb-6">
    <Label for="username" class="mb-2">{$translations.usernameTitle}</Label>
    <Input type="text" id="username" name="username" bind:value={$data.username} required />
    <Helper class="mt-2" color="red">
      {#if $errors.username}
        {$errors.username}
      {/if}
    </Helper>
  </div>
  <div class="mb-6">
    <Label for="password" class="mb-2">{$translations.passwordTitle}</Label>
    <Input type="password" id="password" name="password" bind:value={$data.password} required />
    <Helper class="mt-2" color="red">
      {#if $errors.password}
        {$errors.password}
      {/if}
    </Helper>
  </div>
  {#if errorMessage}
    <div class="mb-4">
      <P color="text-red-700 dark:text-red-500">{errorMessage}</P>
    </div>
  {/if}
  <Button type="submit">{$translations.applyChangesTitle}</Button>
</form>
