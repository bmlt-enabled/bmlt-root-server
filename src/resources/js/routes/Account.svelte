<script lang="ts">
  import { AccordionItem, Accordion, Button, Helper, Input, Label, Listgroup, Table, TableBody, TableBodyCell, TableBodyRow } from 'flowbite-svelte';
  import { createEventDispatcher } from 'svelte';
  import { createForm } from 'felte';
  // svelte-hack' -- import hacked to get onMount to work correctly for unit tests
  import { onMount } from 'svelte/internal';
  import { validator } from '@felte/validator-yup';
  import * as yup from 'yup';

  import type { ServiceBody } from 'bmlt-root-server-client';

  import { authenticatedUser } from '../stores/apiCredentials';
  import { formIsDirty } from '../lib/utils';
  import Nav from '../components/NavBar.svelte';
  import RootServerApi from '../lib/RootServerApi';
  import { spinner } from '../stores/spinner';
  import { translations } from '../stores/localization';
  import type { User } from 'bmlt-root-server-client';

  let serviceBodies: ServiceBody[] = [];
  let serviceBodiesLoaded = false;
  let associatedServiceBodyNames: string[] = [];

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
    // just leave type and ownerId alone (they would never be changed here)
    email: $authenticatedUser?.email ?? '',
    username: $authenticatedUser?.username ?? '',
    description: $authenticatedUser?.description ?? '',
    password: ''
  };
  let savedUser: User;

  const { data, errors, form, isDirty } = createForm({
    initialValues: initialValues,
    onSubmit: async (values) => {
      spinner.show();
      if ($authenticatedUser) {
        await RootServerApi.partialUpdateUser($authenticatedUser.id, values);
        savedUser = await RootServerApi.getUser($authenticatedUser.id);
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

  async function getServiceBodies(): Promise<void> {
    try {
      spinner.show();
      serviceBodies = await RootServerApi.getServiceBodies();
      serviceBodiesLoaded = true;
    } catch (error: any) {
      await RootServerApi.handleErrors(error);
    } finally {
      spinner.hide();
    }
  }

  // helper function to compute the set of service bodies that the currently logged in user can edit.
  // s is the starting service body
  // children is an array of sets of children, indexed by the id of the parent service body
  // associatedServiceBodies is the set of service bodies that is being accumulated
  function recursivelyAddServiceBodies(s: ServiceBody, children: Set<ServiceBody>[], associatedServiceBodies: Set<ServiceBody>) {
    associatedServiceBodies.add(s);
    if (children[s.id]) {
      for (const c of children[s.id]) {
        recursivelyAddServiceBodies(c, children, associatedServiceBodies);
      }
    }
  }

  onMount(() => {
    // we only show the service bodies that can be edited if this is a serviceBodyAdmin
    if ($authenticatedUser?.type === 'serviceBodyAdmin') {
      getServiceBodies();
    }
  });

  $: {
    const id = $authenticatedUser?.id;
    if (serviceBodiesLoaded && id) {
      // children is an array with indices = service body ids, values a set of children of that service body
      // (not recursively - the recursion is handled elsewhere)
      const children: Set<ServiceBody>[] = [];
      for (const s of serviceBodies) {
        const p = s.parentId;
        if (p) {
          if (children[p]) {
            children[p].add(s);
          } else {
            children[p] = new Set([s]);
          }
        }
      }
      const associatedServiceBodies: Set<ServiceBody> = new Set();
      for (const s of serviceBodies) {
        if (s.adminUserId === id || s.assignedUserIds.includes(id)) {
          recursivelyAddServiceBodies(s, children, associatedServiceBodies);
        }
      }
      associatedServiceBodyNames = Array.from(associatedServiceBodies)
        .map((s) => s.name)
        .sort();
    }
  }

  // This hack is required until https://github.com/themesberg/flowbite-svelte/issues/1395 is fixed.
  function disableButtonHack(event: MouseEvent) {
    if (!$isDirty) {
      event.preventDefault();
    }
  }

  $: isDirty.set(formIsDirty(initialValues, $data));
</script>

<Nav />

<div class="mx-auto max-w-3xl p-2">
  <h2 class="mb-4 text-center text-xl font-semibold dark:text-white">{$translations.accountSettingsTitle}</h2>
  <Table>
    <TableBody>
      <TableBodyRow>
        <TableBodyCell>{$translations.accountTypeTitle}</TableBodyCell>
        <TableBodyCell>
          {userType}
        </TableBodyCell>
      </TableBodyRow>
      {#if $authenticatedUser?.type !== 'admin'}
        <TableBodyRow>
          <TableBodyCell>
            {$translations.nameTitle}
          </TableBodyCell>
          <TableBodyCell>
            {$authenticatedUser?.displayName}
          </TableBodyCell>
        </TableBodyRow>
        <TableBodyRow>
          <TableBodyCell>
            {$translations.usernameTitle}
          </TableBodyCell>
          <TableBodyCell>
            {$authenticatedUser?.username}
          </TableBodyCell>
        </TableBodyRow>
        {#if $authenticatedUser?.type === 'serviceBodyAdmin'}
          <TableBodyRow>
            <TableBodyCell colSpan={2}>
              <Accordion>
                <AccordionItem>
                  <span slot="header">{$translations.associatedServiceBodies}</span>
                  {#if !serviceBodiesLoaded}
                    {$translations.loading}
                  {:else if associatedServiceBodyNames.length === 0}
                    {$translations.none}
                  {:else}
                    <Listgroup items={associatedServiceBodyNames} let:item>
                      {item}
                    </Listgroup>
                  {/if}
                </AccordionItem>
              </Accordion>
            </TableBodyCell>
          </TableBodyRow>
        {/if}
      {/if}
    </TableBody>
  </Table>
  <p>&nbsp;</p>
  <form use:form>
    <div class="md grid gap-4">
      <div class={$authenticatedUser?.type !== 'admin' ? 'hidden' : ''}>
        <div class="md">
          <Label for="displayName" class="mb-2">{$translations.nameTitle}</Label>
          <Input type="text" id="displayName" name="displayName" required />
          <Helper class="mt-2" color="red">
            {#if $errors.displayName}
              {$errors.displayName}
            {/if}
          </Helper>
        </div>
        <div class="md">
          <Label for="username" class="mb-2">{$translations.usernameTitle}</Label>
          <Input type="text" id="username" name="username" required />
          <Helper class="mt-2" color="red">
            {#if $errors.username}
              {$errors.username}
            {/if}
          </Helper>
        </div>
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
      <div class="md">
        <Button type="submit" class="w-full" disabled={!$isDirty} on:click={disableButtonHack}>
          {$translations.applyChangesTitle}
        </Button>
      </div>
    </div>
  </form>
</div>
