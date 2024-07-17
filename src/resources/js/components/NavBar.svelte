<script lang="ts">
  import { Navbar, NavBrand, NavLi, NavUl, NavHamburger } from 'flowbite-svelte';

  import DarkMode from './DarkMode.svelte';
  import { apiCredentials } from '../stores/apiCredentials';
  import { translations } from '../stores/localization';

  const globalSettings = settings;

  async function logout(event: Event) {
    event.preventDefault();
    await apiCredentials.logout();
  }
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
    <NavLi href="#/">{$translations.homeTitle}</NavLi>
    <NavLi href="#/meetings">{$translations.meetingsTitle}</NavLi>
    <!--  // TODO:  this currently hides nav item but user can still access route-->
    <!--  // TODO:  although it will say no users. Seems like we could update route guard to account for this -->
    <!--    {#if $usersData.length > 1 || $authenticatedUser?.type === 'admin'}-->
    <!--      <NavLi href="#/users">{$translations.usersTitle}</NavLi>-->
    <!--    {/if}-->
    <!--    {#if $serviceBodiesData.length > 0 || $authenticatedUser?.type === 'admin'}-->
    <!--      <NavLi href="#/service-bodies">{$translations.serviceBodiesTitle}</NavLi>-->
    <!--    {/if}-->
    <NavLi href="#/users">{$translations.usersTitle}</NavLi>
    <NavLi href="#/service-bodies">{$translations.serviceBodiesTitle}</NavLi>
    <NavLi href="#" on:click={logout}>{$translations.logout}</NavLi>
  </NavUl>
</Navbar>
