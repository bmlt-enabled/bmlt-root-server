<script lang="ts">
  import { writable } from 'svelte/store';
  import { push } from 'svelte-spa-router';
  import RootServerApi from '../lib/RootServerApi';
  import { Helper, Label, Input, InputAddon, ButtonGroup } from 'flowbite-svelte';
  import { UserCircleSolid } from 'flowbite-svelte-icons';

  const authenticationMessage = writable('');
  const validationMessage = writable({ username: '', password: '' });
  const username = writable('');
  const password = writable('');

  const handleOnSubmit = async () => {
    try {
      const token = await RootServerApi.login($username, $password);
      RootServerApi.token = token;
      push('/'); // send home
    } catch (error: any) {
      validationMessage.set({
        username: '',
        password: ''
      });
      authenticationMessage.set('');
      await RootServerApi.handleErrors(error, {
        handleAuthenticationError: (error) => authenticationMessage.set(error.message),
        handleValidationError: (error) => {
          validationMessage.set({
            username: (error?.errors?.username ?? []).join(' '),
            password: (error?.errors?.password ?? []).join(' ')
          });
        }
      });
    }
  };
</script>

<form on:submit|preventDefault={handleOnSubmit}>
  <div class="mb-6">
    <Label for="website-admin" class="mb-2 block">Username</Label>
    <ButtonGroup class="w-full">
      <InputAddon>
        <UserCircleSolid class="h-4 w-4 text-gray-500 dark:text-gray-400" />
      </InputAddon>
      <Input id="website-admin" bind:value={$username} placeholder="username" />
    </ButtonGroup>
    <Helper class="mt-2" color="red"><span class="font-medium">{$validationMessage.username}</span></Helper>
    <Label for="website-admin" class="mb-2 block">Password</Label>
    <ButtonGroup class="w-full">
      <InputAddon>
        <UserCircleSolid class="h-4 w-4 text-gray-500 dark:text-gray-400" />
      </InputAddon>
      <Input id="website-admin" bind:value={$password} placeholder="Password" />
    </ButtonGroup>
    <Helper class="mt-2" color="red"><span class="font-medium">{$validationMessage.password}</span></Helper>
    <Helper class="mt-2" color="red"><span class="font-medium">{$authenticationMessage}</span></Helper>
    <button class="rounded-full bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700">Login</button>
  </div>
</form>
