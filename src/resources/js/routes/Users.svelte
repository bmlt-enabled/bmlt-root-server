<script lang="ts">
  import { validator } from '@felte/validator-yup';
  import { createForm } from 'felte';
  import { Button, Helper, Input, Label, P, Select } from 'flowbite-svelte';
  import * as yup from 'yup';

  import Nav from '../components/NavBar.svelte';
  import { authenticatedUser } from '../stores/apiCredentials';
  import { spinner } from '../stores/spinner';
  import { translations } from '../stores/localization';
  import RootServerApi from '../lib/RootServerApi';
  import { onMount } from 'svelte';

  const { form, data, errors } = createForm({
    initialValues: {
      user: '',
      userIs: '',
      ownedBy: '',
      email: '',
      name: '',
      username: '',
      password: '',
      description: ''
    },
    onSubmit: async (values) => {
      spinner.show();
      console.log(values);
      spinner.hide();
    },
    extend: validator({
      schema: yup.object({
        user: yup.string().required('User is required'),
        userIs: yup.string().required('User role is required'),
        ownedBy: yup.string().required('Owner is required'),
        email: yup.string().email('Invalid email').required('Email is required'),
        name: yup.string().required('Name is required'),
        username: yup.string().required('Username is required'),
        password: yup.string().required('Password is required'),
        description: yup.string()
      })
    })
  });

  let users = [];
  let selectedUser = null;
  let errorMessage = '';

  const getUsers = async (): Promise<void> => {
    if ($authenticatedUser?.type !== 'admin' && $authenticatedUser?.type !== 'serviceBodyAdmin') {
      return;
    }
    try {
      const allUsers = await RootServerApi.getUsers();
      allUsers.sort((a, b) => a.displayName.localeCompare(b.displayName));

      if ($authenticatedUser?.type === 'admin') {
        for (const u of allUsers) {
          if (u.type !== 'admin' && u.ownerId === null) {
            u.ownerId = $authenticatedUser.id.toString();
          }
        }
      }

      users = allUsers;
    } catch (error: any) {
      RootServerApi.handleErrors(error, {
        handleError: (error) => {
          errorMessage = error.message;
        }
      });
    }
  };

  onMount(() => {
    getUsers();
  });

  $: if (selectedUser) {
    const user = users.find((u) => u.username === selectedUser);
    if (user) {
      $data.userIs = user.type;
      $data.ownedBy = user.ownerId ? user.ownerId.toString() : '';
      $data.email = user.email;
      $data.name = user.displayName;
      $data.username = user.username;
      $data.description = user.description;
      $data.password = ''; // Clear password field for security reasons
    }
  }

  const userIsOptions = [
    { value: 'serviceBodyAdmin', name: 'Service Body Administrator' },
    { value: 'observer', name: 'Observer' },
    { value: 'deactivated', name: 'Deactivated' }
  ];

  $: userOptions = users.map((user) => ({ value: user.username, name: user.displayName }));
  $: ownedByOptions = users.map((user) => ({ value: user.id.toString(), name: user.displayName }));
</script>

<Nav />

{#if $authenticatedUser}
  <div class="mx-auto max-w-xl p-4">
    <h2 class="mb-4 text-center text-xl font-semibold dark:text-white">{$translations.userTitle} {$translations.idTitle} #{$authenticatedUser.id}</h2>
    <form use:form>
      <div class="mb-6">
        <Label for="user" class="mb-2">{$translations.userTitle}</Label>
        <Select id="user" items={userOptions} name="user" bind:value={selectedUser} />
        <Helper class="mt-2" color="red">
          {#if $errors.user}
            {$errors.user}
          {/if}
        </Helper>
      </div>
      <div class="mb-6 grid gap-6 md:grid-cols-2">
        <div>
          <Label for="user-is" class="mb-2">{$translations.userIsATitle}</Label>
          <Select id="user-is" items={userIsOptions} name="userIs" bind:value={$data.userIs} />
          <Helper class="mt-2" color="red">
            {#if $errors.userIs}
              {$errors.userIs}
            {/if}
          </Helper>
        </div>
        <div>
          <Label for="owned-by" class="mb-2">{$translations.ownedByTitle}</Label>
          <Select id="owned-by" items={ownedByOptions} name="ownedBy" bind:value={$data.ownedBy} />
          <Helper class="mt-2" color="red">
            {#if $errors.ownedBy}
              {$errors.ownedBy}
            {/if}
          </Helper>
        </div>
      </div>
      <div class="mb-6">
        <Label for="email" class="mb-2">{$translations.emailTitle}</Label>
        <Input type="email" id="email" name="email" bind:value={$data.email} required />
        <Helper class="mt-2" color="red">
          {#if $errors.email}
            {$errors.email}
          {/if}
        </Helper>
      </div>
      <div class="mb-6">
        <Label for="name" class="mb-2">{$translations.nameTitle}</Label>
        <Input type="text" id="name" name="name" bind:value={$data.name} required />
        <Helper class="mt-2" color="red">
          {#if $errors.name}
            {$errors.name}
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
  </div>
{/if}
