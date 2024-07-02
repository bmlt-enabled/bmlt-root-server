<script lang="ts">
  import Router from 'svelte-spa-router';
  import Home from './routes/Home.svelte';
  import Login from './routes/Login.svelte';
  import Logout from './routes/Logout.svelte';
  import Meetings from './routes/Meetings.svelte';
  import { restoreLanguage } from './localization/localization';

  restoreLanguage();

  const routes = {
    '/login': Login,
    '/logout': Logout,
    '/meetings': Meetings,
    '*': Home
  };
</script>

<svelte:head>
  <title>BMLT Root Server</title>
</svelte:head>

<Router {routes} />
