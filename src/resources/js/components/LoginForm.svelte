<script lang="ts">
  import { writable } from 'svelte/store';
  import { push } from 'svelte-spa-router';
  import RootServerApi from '../lib/RootServerApi';
  import { DarkMode } from 'flowbite-svelte';
  import { strings } from '../localization/localization';
  /*global settings */
  let globalSettings = settings;
  const languageOptions = writable(Object.entries(globalSettings.languageMapping));
  const selectedLanguage = writable(globalSettings.defaultLanguage);

  if (!localStorage.language) {
    localStorage.language = globalSettings.defaultLanguage;
  }

  selectedLanguage.set(localStorage.language);

  const handleLanguageChange = (event: Event) => {
    const target = event.target as HTMLSelectElement;
    localStorage.language = target.value;
    selectedLanguage.set(target.value);
  };

  const authenticationMessage = writable('');
  const validationMessage = writable({ username: '', password: '' });
  const username = writable('');
  const password = writable('');

  const handleOnSubmit = async () => {
    try {
      const token = await RootServerApi.login($username, $password);
      RootServerApi.token = token;
      push('/'); // send home
    } catch (error: any) {
      validationMessage.set({
        username: '',
        password: ''
      });
      authenticationMessage.set('');
      await RootServerApi.handleErrors(error, {
        handleAuthenticationError: (error) => authenticationMessage.set(error.message),
        handleValidationError: (error) => {
          validationMessage.set({
            username: (error?.errors?.username ?? []).join(' '),
            password: (error?.errors?.password ?? []).join(' ')
          });
        }
      });
    }
  };
</script>

<section class="bg-gray-50 dark:bg-gray-900">
  <div class="mx-auto flex flex-col items-center justify-center px-6 py-8 md:h-screen lg:py-0">
    <div class="mb-6 flex items-center text-2xl font-semibold text-gray-900 dark:text-white">
      BMLT Root Server ({globalSettings.version})
    </div>
    <div class="w-full rounded-lg bg-white shadow sm:max-w-md md:mt-0 xl:p-0 dark:border dark:border-gray-700 dark:bg-gray-800">
      <div class="space-y-4 p-6 sm:p-8 md:space-y-6">
        <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">{strings.adminTitle} {strings.loginTitle}</h1>
        <form on:submit|preventDefault={handleOnSubmit} class="space-y-4 md:space-y-6">
          <div>
            <label for="username" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">{strings.usernameTitle}</label>
            <input
              type="text"
              bind:value={$username}
              id="username"
              class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-gray-900 focus:border-primary-600 focus:ring-primary-600 sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
              placeholder=""
            />
          </div>
          <div class="font-medium text-primary-600 dark:text-primary-500">
            {$validationMessage.username}
          </div>
          <div>
            <label for="password" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">{strings.passwordTitle}</label>
            <input
              type="password"
              bind:value={$password}
              id="password"
              placeholder="••••••••"
              class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-gray-900 focus:border-primary-600 focus:ring-primary-600 sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
            />
          </div>
          <div class="font-medium text-primary-600 dark:text-primary-500">
            {$validationMessage.password}
          </div>
          {#if globalSettings.isLanguageSelectorEnabled}
            <div>
              <label for="language" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Select a language</label>
              <select
                id="language"
                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                on:change={handleLanguageChange}
                bind:value={$selectedLanguage}
              >
                <option value="" disabled>Choose a language</option>
                {#each $languageOptions as [code, name]}
                  <option value={code}>{name}</option>
                {/each}
              </select>
            </div>
          {/if}
          <button
            type="submit"
            class="w-full rounded-lg bg-primary-600 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800"
            >Sign in</button
          >
          <p class="font-medium text-primary-600 dark:text-primary-500">
            {$authenticationMessage}
          </p>
        </form>
      </div>
    </div>
    <DarkMode class="ml-4" />
  </div>
</section>
