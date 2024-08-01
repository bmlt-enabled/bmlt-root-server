<script lang="ts">
  // TODO: remove this statement after Svelte 5 is out. This is a hack to avoid getting spurious warnings
  // about unknown props -- it tricks the Svelte runtime into thinking that we are using 'params' for something.
  // Svelte 5 is supposed to fix this problem (see https://github.com/sveltejs/svelte/issues/4652).
  // We also need to tell the linter it's ok that we aren't using the value of $$restProps
  // eslint-disable-next-line
  $$restProps;

  import Nav from '../components/NavBar.svelte';
  import { Card } from 'flowbite-svelte';
  import { authenticatedUser } from '../stores/apiCredentials';
</script>

<Nav />

{#if $authenticatedUser}
  <Card class="mx-auto my-8 w-full max-w-lg bg-white p-8 text-center shadow-lg dark:bg-gray-800">
    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
      Welcome {$authenticatedUser.displayName}
    </h5>
  </Card>
{/if}
