<script lang="ts">
  import './css/main.scss';
  import Router from 'svelte-spa-router';
  import Home from './routes/Home.svelte';
  import Login from './routes/Login.svelte';

  const routes = {
    '/login': Login,
    '*': Home
  };
</script>

<svelte:head>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <title>BMLT Root Server</title>
</svelte:head>

<body>
  <Router {routes} />
</body>
