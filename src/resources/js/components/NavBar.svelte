<script lang="ts">
  import { onMount } from 'svelte';
  import { DarkMode, Navbar, NavBrand, NavLi, NavUl, NavHamburger } from 'flowbite-svelte';
  import { isLoggedIn, isTokenExpired, checkAuth } from '../stores/authStore';
  /*global settings */
  let globalSettings = settings;
  onMount(() => {
    checkAuth();
  });

  $: showLogin = !$isLoggedIn || $isTokenExpired;
</script>

<Navbar>
  <NavHamburger />
  <div class="ml-auto flex items-center">
    <DarkMode size="lg" class="inline-block hover:text-gray-900 dark:hover:text-white" />
  </div>
  <NavBrand href="#/">
    <span class="self-center whitespace-nowrap text-xl font-semibold dark:text-white">BMLT ({globalSettings.version})</span>
  </NavBrand>
  <NavUl>
    <NavLi href="#/">Home</NavLi>
    <NavLi href="#/meetings">Meetings</NavLi>
    {#if showLogin}
      <NavLi href="#/login">Login</NavLi>
    {:else}
      <NavLi href="#/logout">Logout</NavLi>
    {/if}
  </NavUl>
</Navbar>
