<script lang="ts">
  import { writable } from 'svelte/store';
  import { push } from 'svelte-spa-router';
  import RootServerApi from '../lib/RootServerApi';

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
  <section>
    <input type="text" bind:value={$username} placeholder="Username" />
    <span class="form-txt">{$validationMessage.username}</span>
    <input type="password" bind:value={$password} placeholder="Password" />
    <span class="form-txt">{$validationMessage.password}</span>
    <button class="form-txt">Login</button>
    <span class="form-txt">{$authenticationMessage}</span>
  </section>
</form>
