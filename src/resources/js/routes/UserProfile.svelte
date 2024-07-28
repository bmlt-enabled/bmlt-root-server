<script lang="ts">
  import Nav from '../components/NavBar.svelte';

  import { translations } from '../stores/localization';
  import UserProfileForm from '../components/UserProfileForm.svelte';
</script>

<Nav />

<div class="mx-auto max-w-3xl p-2">
  <h2 class="mb-4 text-center text-xl font-semibold dark:text-white">{$translations.userProfileTitle}</h2>
  <UserProfileForm />
</div>
