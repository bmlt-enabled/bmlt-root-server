<script>
  import { DarkMode, Navbar, NavBrand, NavLi, NavUl, NavHamburger } from 'flowbite-svelte';
  /*global settings */
  let globalSettings = settings;
</script>

<Navbar>
  <NavBrand href="#/">
    <span class="self-center whitespace-nowrap text-xl font-semibold dark:text-white">BMLT ({globalSettings.version})</span>
  </NavBrand>
  <NavHamburger />
  <NavUl>
    <NavLi href="#/">Home</NavLi>
    <NavLi href="#/login">Login</NavLi>
    <NavLi href="#/about">About</NavLi>
  </NavUl>
  <DarkMode class="ml-4" />
</Navbar>
