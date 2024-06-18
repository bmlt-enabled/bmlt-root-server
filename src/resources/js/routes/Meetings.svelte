<script lang="ts">
  import Nav from '../components/NavBar.svelte';
  import { onMount } from 'svelte';
  import { replace } from 'svelte-spa-router';
  import { isLoggedIn, isTokenExpired, checkAuth } from '../stores/authStore';
  import { Card } from 'flowbite-svelte';

  onMount(() => {
    checkAuth();
  });

  $: if ($isTokenExpired || !$isLoggedIn) {
    replace('/login');
  }
</script>

<Nav />
<Card class="mx-auto my-8 w-full max-w-lg bg-white p-8 text-center shadow-lg dark:bg-gray-800">
  <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Meetings</h5>
</Card>
