<script lang="ts">
  import Router from 'svelte-spa-router';
  import { push } from 'svelte-spa-router';
  import { wrap } from 'svelte-spa-router/wrap';
  import type { ConditionsFailedEvent } from 'svelte-spa-router';

  import Home from './routes/Home.svelte';
  import Login from './routes/Login.svelte';
  import Meetings from './routes/Meetings.svelte';
  import Users from './routes/Users.svelte';
  import SpinnerModal from './components/SpinnerModal.svelte';
  import { apiCredentials } from './stores/apiCredentials';

  const routes = {
    '/login': Login,
    '/meetings': wrap({
      component: Meetings,
      conditions: [requiresAuthentication]
    }),
    '/users': wrap({
      component: Users,
      conditions: [requiresAuthentication]
    }),
    '*': wrap({
      component: Home,
      conditions: [requiresAuthentication]
    })
  };

  function requiresAuthentication(): boolean {
    return !!$apiCredentials;
  }

  function conditionsFailed(event: ConditionsFailedEvent) {
    if (Object.keys(routes).includes(event.detail.location)) {
      push(`/login?route=${event.detail.location}`);
    } else {
      push('/login');
    }
  }

  $: if (!$apiCredentials) {
    push('/login');
  }
</script>

<svelte:head>
  <title>BMLT Root Server</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
</svelte:head>

<Router {routes} on:conditionsFailed={conditionsFailed} />
<SpinnerModal />
