<script lang="ts">
  import { onMount } from 'svelte';
  import LoginForm from '../components/LoginForm.svelte';
  import { replace } from 'svelte-spa-router';
  import { isLoggedIn, isTokenExpired, checkAuth } from '../stores/authStore';

  onMount(() => {
    checkAuth();
  });

  $: if (!$isTokenExpired && $isLoggedIn) {
    replace('/');
  }
</script>

<LoginForm />
