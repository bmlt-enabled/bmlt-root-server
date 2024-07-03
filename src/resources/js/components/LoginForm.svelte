<script lang="ts">
  import { validator } from '@felte/validator-yup';
  import { createForm } from 'felte';
  import { Button, DarkMode, Helper, Input, Label, P, Select } from 'flowbite-svelte';
  import { push } from 'svelte-spa-router';
  import * as yup from 'yup';

  import RootServerApi from '../lib/RootServerApi';
  import { translations } from '../stores/localization';
  import type { ApiCredentialsStore } from '../stores/apiCredentials';

  export let apiCredentials: ApiCredentialsStore;

  const globalSettings = settings;
  const languageOptions = Object.entries(globalSettings.languageMapping).map((lang) => ({ value: lang[0], name: lang[1] }));
  let selectedLanguage = translations.getLanguage();
  let errorMessage: string | undefined;

  const { form, data, errors } = createForm({
    initialValues: {
      username: '',
      password: ''
    },
    onSubmit: async (values) => {
      await apiCredentials.login(values.username, values.password);
    },
    onSuccess: () => {
      push('/');
    },
    onError: async (error) => {
      await RootServerApi.handleErrors(error as Error, {
        handleAuthenticationError: () => {
          errorMessage = $translations.invalidUsernameOrPassword;
        },
        handleValidationError: (error) => {
          errors.set({
            username: (error?.errors?.username ?? []).join(' '),
            password: (error?.errors?.password ?? []).join(' ')
          });
        }
      });
    },
    extend: validator({
      schema: yup.object({
        username: yup.string().required(),
        password: yup.string().required()
      })
    })
  });

  $: if (selectedLanguage) {
    translations.setLanguage(selectedLanguage);
  }

  $: if ($data) {
    errorMessage = '';
  }
</script>

<div class="mx-auto flex flex-col items-center justify-center px-6 py-8 md:h-screen lg:py-0">
  <div class="mb-6 flex items-center text-2xl font-semibold text-gray-900 dark:text-white">
    {$translations.rootServerTitle} ({globalSettings.version})
  </div>
  <div class="w-full rounded-lg bg-white shadow sm:max-w-md md:mt-0 xl:p-0 dark:border dark:border-gray-700 dark:bg-gray-800">
    <div class="m-8">
      <form use:form>
        <div class="mb-4">
          <Label for="username" class="mb-2">{$translations.usernameTitle}</Label>
          <Input type="text" name="username" />
          <Helper class="mt-2" color="red">
            {#if $errors.username}
              {$errors.username}
            {/if}
          </Helper>
        </div>
        <div class="mb-4">
          <Label for="password" class="mb-2">{$translations.passwordTitle}</Label>
          <Input type="password" name="password" />
          <Helper class="mt-2" color="red">
            {#if $errors.password}
              {$errors.password}
            {/if}
          </Helper>
        </div>
        {#if globalSettings.isLanguageSelectorEnabled}
          <div class="mb-4">
            <Label for="languageSelection" class="mb-2">{$translations.languageSelectTitle}</Label>
            <Select items={languageOptions} bind:value={selectedLanguage} />
          </div>
        {/if}
        {#if errorMessage}
          <div class="mb-4">
            <P color="text-red-700 dark:text-red-500">{errorMessage}</P>
          </div>
        {/if}
        <div class="mb-2">
          <Button type="submit">{$translations.loginVerb}</Button>
        </div>
      </form>
    </div>
  </div>
  <DarkMode class="ml-4" />
</div>
