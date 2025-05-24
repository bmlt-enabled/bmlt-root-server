<script lang="ts">
  import { Navbar, NavBrand, NavLi, NavUl, NavHamburger } from 'flowbite-svelte';

  import DarkMode from './DarkMode.svelte';
  import { apiCredentials } from '../stores/apiCredentials';
  import { authenticatedUser } from '../stores/apiCredentials';
  import { translations } from '../stores/localization';

  const globalSettings = settings;

  async function logout(event: Event) {
    event.preventDefault();
    await apiCredentials.logout();
  }
</script>

<Navbar class="dark:bg-gray-900">
  <NavHamburger />
  <div class="ml-auto flex items-center">
    <DarkMode size="lg" class="inline-block hover:text-gray-900 dark:hover:text-white" />
  </div>
  <NavBrand href="#/">
    <span class="self-center text-xl font-semibold whitespace-nowrap dark:text-white">BMLT ({globalSettings.version})</span>
  </NavBrand>
  <NavUl>
    <NavLi href="#/">{$translations.homeTitle}</NavLi>
    <NavLi href="#/meetings">{$translations.meetingsTitle}</NavLi>
    {#if $authenticatedUser?.type === 'admin'}
      <NavLi href="#/formats">{$translations.formatsTitle}</NavLi>
    {/if}
    {#if $authenticatedUser?.type !== 'observer'}
      <NavLi href="#/servicebodies">{$translations.serviceBodiesTitle}</NavLi>
      <NavLi href="#/users">{$translations.usersTitle}</NavLi>
    {/if}
    <NavLi href="#/account">{$translations.accountTitle}</NavLi>
    <NavLi href="#" onclick={logout}>{$translations.logout}</NavLi>
  </NavUl>
</Navbar>
