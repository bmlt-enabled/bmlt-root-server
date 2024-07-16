<script lang="ts">
  import { querystring, replace } from 'svelte-spa-router';

  import { apiCredentials } from '../stores/apiCredentials';
  import { fetchServiceBodies } from '../stores/serviceBodies';
  import { fetchUsers } from '../stores/users';
  import LoginForm from '../components/LoginForm.svelte';
  // svelte-hack' -- import hacked to get onMount to work correctly for unit tests
  import { onMount } from 'svelte/internal';

  function redirect() {
    replace($querystring?.startsWith('route=') ? $querystring.slice(6) : '/');
  }

  async function handleAuthenticated() {
    await fetchUsers();
    await fetchServiceBodies();
    redirect();
  }

  onMount(() => {
    // If someone browses to this page while we are already logged in, just redirect
    if ($apiCredentials) {
      handleAuthenticated();
    }
  });
</script>

<LoginForm {apiCredentials} on:authenticated={handleAuthenticated} />
