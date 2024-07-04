<script lang="ts">
  import Router from 'svelte-spa-router';

  import LoginForm from './components/LoginForm.svelte';
  import Home from './routes/Home.svelte';
  import Meetings from './routes/Meetings.svelte';
  import SpinnerModal from './components/SpinnerModal.svelte';
  import { apiCredentials } from './stores/apiCredentials';

  const routes = {
    '/meetings': Meetings,
    '*': Home
  };
</script>

<svelte:head>
  <title>BMLT Root Server</title>
</svelte:head>

{#if $apiCredentials}
  <Router {routes} />
{:else}
  <LoginForm {apiCredentials} />
{/if}
<SpinnerModal />
