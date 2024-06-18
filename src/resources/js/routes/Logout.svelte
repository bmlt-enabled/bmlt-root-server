<script lang="ts">
  import Nav from '../components/NavBar.svelte';
  import { replace } from 'svelte-spa-router';
  import { isLoggedIn, isTokenExpired } from '../stores/authStore';
  import RootServerApi from '../lib/RootServerApi';

  localStorage.removeItem('token');
  isLoggedIn.set(false);
  isTokenExpired.set(true);
  RootServerApi.logout();
  replace('#/login');
</script>

<Nav />
