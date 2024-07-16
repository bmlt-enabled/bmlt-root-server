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
    <NavLi href="#/users">{$translations.usersTitle}</NavLi>
    <NavLi href="#/service-bodies">{$translations.serviceBodiesTitle}</NavLi>
    <NavLi href="#" on:click={logout}>{$translations.logout}</NavLi>
  </NavUl>
</Navbar>
