<script lang="ts">
  import { AccordionItem, Accordion, Button, Helper, Input, Label } from 'flowbite-svelte';
  import { createEventDispatcher } from 'svelte';
  import { createForm } from 'felte';
  import { validator } from '@felte/validator-yup';
  import * as yup from 'yup';

  import AccountServiceBodyList from '../components/AccountServiceBodyList.svelte';
  import { authenticatedUser } from '../stores/apiCredentials';
  import { formIsDirty } from '../lib/utils';
  import Nav from '../components/NavBar.svelte';
  import RootServerApi from '../lib/RootServerApi';
  import { spinner } from '../stores/spinner';
  import { translations } from '../stores/localization';
  import type { User } from 'bmlt-root-server-client';

  const dispatch = createEventDispatcher<{ saved: { user: User } }>();
  let userType = 'unknown';
  switch ($authenticatedUser?.type) {
    case 'serviceBodyAdmin':
      userType = $translations.serviceBodyAdminTitle;
      break;
    case 'admin':
      userType = $translations.serverAdministratorTitle;
      break;
    case 'observer':
      userType = $translations.observerTitle;
      break;
    case 'deactivated':
      userType = $translations.deactivatedUserTitle;
      break;
  }
  const initialValues = {
    displayName: $authenticatedUser?.displayName ?? '',
    userType: userType, // this isn't part of the UserUpdate type, and is read-only in the form
    email: $authenticatedUser?.email ?? '',
    username: $authenticatedUser?.username ?? '',
    description: $authenticatedUser?.description ?? '',
    password: ''
    // type and ownerId aren't changed and aren't in the form (we're using partialUserUpdate)
  };
  let savedUser: User;
  let savedData: { displayName: string; userType: string; email: string; username: string; description: string; password: string };

  const { data, errors, form, isDirty, reset } = createForm({
    initialValues: initialValues,
    onSubmit: async (values) => {
      spinner.show();
      if ($authenticatedUser) {
        await RootServerApi.partialUpdateUser($authenticatedUser.id, values);
        savedUser = await RootServerApi.getUser($authenticatedUser.id);
        savedData = $data;
        // the following check is needed so that svelte notices the form is dirty if the user edits something
        // other than the password, saves, and then edits just the password field
        if (!savedData?.password) {
          savedData.password = '';
        }
        authenticatedUser.set(savedUser);
      } else {
        // this should never happen
        throw new Error('internal error - trying to update account information without an authenticated user');
      }
    },
    onError: async (error) => {
      console.log(error);
      await RootServerApi.handleErrors(error as Error, {
        handleValidationError: (error) => {
          errors.set({
            email: (error?.errors?.email ?? []).join(' '),
            displayName: (error?.errors?.displayName ?? []).join(' '),
            username: (error?.errors?.username ?? []).join(' '),
            password: (error?.errors?.password ?? []).join(' '),
            description: (error?.errors?.description ?? []).join(' ')
          });
        }
      });
      spinner.hide();
    },
    onSuccess: () => {
      spinner.hide();
      dispatch('saved', { user: savedUser });
    },
    extend: validator({
      schema: yup.object({
        email: yup.string().max(255).email(),
        displayName: yup
          .string()
          .transform((v) => v.trim())
          .max(255)
          .required(),
        username: yup
          .string()
          .transform((v) => v.trim())
          .max(255)
          .required(),
        password: yup
          .string()
          .transform((v) => (v ? v : undefined))
          .test('validatePassword', 'password must be between 12 and 255 characters', (password) => {
            const isEditing = $authenticatedUser !== null;
            if (!password) {
              return isEditing ? true : false;
            }

            if (password.length < 12) {
              return false;
            }

            if (password.length > 255) {
              return false;
            }

            return true;
          }),
        description: yup
          .string()
          .transform((v) => v.trim())
          .max(255)
      }),
      castValues: true
    })
  });

  // This hack is required until https://github.com/themesberg/flowbite-svelte/issues/1395 is fixed.
  function disableButtonHack(event: MouseEvent) {
    if (!$isDirty) {
      event.preventDefault();
    }
  }

  $: isDirty.set(savedData ? formIsDirty(savedData, $data) : formIsDirty(initialValues, $data));
</script>

<Nav />

<div class="mx-auto max-w-3xl p-2">
  <h2 class="mb-4 text-center text-xl font-semibold dark:text-white">{$translations.accountSettingsTitle}</h2>
  <form use:form>
    <div class="md grid gap-4">
      <div class="md">
        <Label for="displayName" class="mb-2">{$translations.nameTitle}</Label>
        <Input type="text" id="displayName" name="displayName" required disabled={$authenticatedUser?.type !== 'admin'} />
        <Helper class="mt-2" color="red">
          {#if $errors.displayName}
            {$errors.displayName}
          {/if}
        </Helper>
      </div>
      <div class="md">
        <Label for="username" class="mb-2">{$translations.usernameTitle}</Label>
        <Input type="text" id="username" name="username" required disabled={$authenticatedUser?.type !== 'admin'} />
        <Helper class="mt-2" color="red">
          {#if $errors.username}
            {$errors.username}
          {/if}
        </Helper>
      </div>
      <div class="md">
        <Label for="userType" class="mb-2">{$translations.accountTypeTitle}</Label>
        <Input type="text" id="userType" name="userType" required disabled />
      </div>
      <div class="md">
        <Label for="email" class="mb-2">{$translations.emailTitle}</Label>
        <Input type="email" id="email" name="email" />
        <Helper class="mt-2" color="red">
          {#if $errors.email}
            {$errors.email}
          {/if}
        </Helper>
      </div>
      <div class="md">
        <Label for="description" class="mb-2">{$translations.descriptionTitle}</Label>
        <Input type="text" id="description" name="description" />
        <Helper class="mt-2" color="red">
          {#if $errors.description}
            {$errors.description}
          {/if}
        </Helper>
      </div>
      <div class="md">
        <Label for="password" class="mb-2">{$translations.passwordTitle}</Label>
        <Input type="password" id="password" name="password" required />
        <Helper class="mt-2" color="red">
          {#if $errors.password}
            {$errors.password}
          {/if}
        </Helper>
      </div>
      <div class="grid gap-4 md:grid-cols-2">
        <div class="w-full">
          <Button type="button" class="w-full" color="red" disabled={!$isDirty} on:click={reset}>
            {$translations.clearFormTitle}
          </Button>
        </div>
        <div class="w-full">
          <Button type="submit" class="w-full" disabled={!$isDirty} on:click={disableButtonHack}>
            {$translations.applyChangesTitle}
          </Button>
        </div>
      </div>
      <Accordion>
        <AccordionItem>
          <span slot="header">{$translations.serviceBodiesWithEditableMeetings}</span>
          <AccountServiceBodyList user={$authenticatedUser} />
        </AccordionItem>
      </Accordion>
    </div>
  </form>
</div>
