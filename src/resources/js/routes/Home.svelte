<script lang="ts">
  import { Card } from 'flowbite-svelte';

  import { authenticatedUser } from '../stores/apiCredentials';
  import Nav from '../components/NavBar.svelte';
  import { translations } from '../stores/localization';
</script>

<Nav />

{#if $authenticatedUser}
  <Card class="mx-auto my-8 w-full max-w-lg bg-white p-8 text-center shadow-lg dark:bg-gray-800">
    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
      {$translations.welcome}
      {$authenticatedUser.displayName}
    </h5>
  </Card>
{/if}
