<script lang="ts">
  import Router from 'svelte-spa-router';

  import LoginForm from './components/LoginForm.svelte';
  import Home from './routes/Home.svelte';
  import Meetings from './routes/Meetings.svelte';
  import Users from './routes/Users.svelte';
  import SpinnerModal from './components/SpinnerModal.svelte';
  import { apiCredentials } from './stores/apiCredentials';

  const routes = {
    '/meetings': Meetings,
    '/users': Users,
    '*': Home
  };
</script>

<svelte:head>
  <title>BMLT Root Server</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
</svelte:head>

{#if $apiCredentials}
  <Router {routes} />
{:else}
  <LoginForm {apiCredentials} />
{/if}
<SpinnerModal />
