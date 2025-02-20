<script lang="ts">
  import { querystring, replace } from 'svelte-spa-router';

  import { apiCredentials } from '../stores/apiCredentials';
  import LoginForm from '../components/LoginForm.svelte';

  import { onMount } from 'svelte';

  function redirect() {
    replace($querystring?.startsWith('route=') ? $querystring.slice(6) : '/');
  }

  onMount(() => {
    // If someone browses to this page while we are already logged in, redirect
    if ($apiCredentials) {
      redirect();
    }
  });
</script>

<LoginForm {apiCredentials} on:authenticated={redirect} />
