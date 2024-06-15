<script lang="ts">
  import Router from 'svelte-spa-router';
  import Home from './routes/Home.svelte';
  import Login from './routes/Login.svelte';

  const routes = {
    '/login': Login,
    '*': Home
  };
</script>

<svelte:head>
  <title>BMLT Root Server</title>
</svelte:head>

<Router {routes} />
