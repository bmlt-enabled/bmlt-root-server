<script lang="ts">

  // TODO: remove this statement after Svelte 5 is out. This is a hack to avoid getting spurious warnings about
  // unknown props -- it tricks the Svelte runtime into thinking that we are using 'params' for something.
  // Svelte 5 is supposed to fix this problem (see https://github.com/sveltejs/svelte/issues/4652).
  $$restProps;

  import Nav from '../components/NavBar.svelte';
  import { onMount } from 'svelte';
  import { replace } from 'svelte-spa-router';
  import { Card } from 'flowbite-svelte';
  import { isLoggedIn, isTokenExpired, checkAuth } from '../stores/authStore';
  import RootServerApi from '../lib/RootServerApi';
  let userInfo: any = null;
  let isLoading = true;

  const fetchUserInfo = async () => {
    const userId = RootServerApi.token?.userId;
    if (userId) {
      try {
        const userData = await RootServerApi.getUser(userId);
        if (userData) {
          userInfo = userData;
        }
      } catch (error: any) {
        console.error('Failed to fetch user information:', error.message);
      }
    }
    isLoading = false;
  };

  onMount(() => {
    checkAuth();
    fetchUserInfo();
  });

  $: if ($isTokenExpired || !$isLoggedIn) {
    replace('/login');
  }
</script>

<Nav />
{#if isLoading}
  <p></p>
{:else}
  <p></p>
  <Card class="mx-auto my-8 w-full max-w-lg bg-white p-8 text-center shadow-lg dark:bg-gray-800">
    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
      Welcome {userInfo?.displayName}
    </h5>
  </Card>
{/if}
