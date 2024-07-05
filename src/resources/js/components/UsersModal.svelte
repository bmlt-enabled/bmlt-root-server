<script lang="ts">
  import { validator } from '@felte/validator-yup';
  import { createForm } from 'felte';
  import { Modal, Button, Helper, Input, Label, Select } from 'flowbite-svelte';
  import type { SizeType } from 'flowbite-svelte';
  import * as yup from 'yup';

  import { spinner } from '../stores/spinner';
  import RootServerApi from '../lib/RootServerApi';
  import type { User } from 'bmlt-root-server-client';
  import { translations } from '../stores/localization';
  import { authenticatedUser } from '../stores/apiCredentials';

  export let showModal: boolean;
  export let selectedUserId: number;
  export let usersById: Record<number, User> = {};

  let size: SizeType = 'sm';

  let userItems = Object.values(usersById)
    .map((user) => ({ value: user.id, name: user.displayName }))
    .sort((a, b) => a.name.localeCompare(b.name));
  const userTypeItems = [
    { value: 'deactivated', name: 'Deactivated' },
    { value: 'observer', name: 'Observer' },
    { value: 'serviceBodyAdmin', name: 'Service Body Administrator' }
  ];

  const { form, data, errors, setInitialValues, reset } = createForm({
    initialValues: {
      type: '',
      ownerId: -1,
      email: '',
      displayName: '',
      username: '',
      password: '',
      description: ''
    },
    onSubmit: async (values) => {
      spinner.show();
      console.log(values);
      // Rather than blindly casing values as a UserCreate object, we should
      // actually build a UserCreate object. That way we are explicit about
      // every field, including the funky ones like ownerId and type.
    },
    onError: async (error) => {
      console.log(error);
      await RootServerApi.handleErrors(error as Error, {
        handleValidationError: (error) => {
          console.log(error);
          // TODO validate that these fields match what is in the 422 error schema
          // from the openapi json spec, and actually try to force these errors to
          // test them.
          errors.set({
            type: (error?.errors?.type ?? []).join(' '),
            ownerId: (error?.errors?.ownerId ?? []).join(' '),
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
    },
    extend: validator({
      // TODO compare these required fields against what is required by the API
      schema: yup.object({
        type: yup.string().required(),
        ownerId: yup.number(),
        email: yup.string().max(255).email(),
        displayName: yup.string().max(255).required(),
        username: yup.string().max(255).required(),
        password: yup.string().test('password-valid', 'password must be between 12 and 255 characters', (password, context) => {
          if (!password) {
            // empty password means no change, which passes validation
            return true;
          }
          if (!password.trim()) {
            return context.createError({ message: 'password must contain non-whitespace characters' });
          }
          return password.length >= 12 && password.length <= 255;
        }),
        description: yup.string().max(255, 'description cannot be longer than 255 characters')
      })
    })
  });

  function populateForm() {
    const user = usersById[selectedUserId];
    // The only reason we use setInitialValues and reesethere instead of setData is to make development
    // easier. It is super annoying that each time we save the file, hot module replacement causes the
    // values in the form fields to be replaced when the UsersForm is refreshed.
    setInitialValues({
      type: user?.type ?? '',
      ownerId: user?.ownerId ? user.ownerId : -1,
      email: user?.email ?? '',
      displayName: user?.displayName ?? '',
      username: user?.username ?? '',
      description: user?.description ?? '',
      password: ''
    });
    reset();
  }

  $: if (selectedUserId) {
    populateForm();
  }
</script>

<Modal bind:open={showModal} {size} autoclose>
  <div class="p-2">
    <p>User ID: {selectedUserId}</p>
    <form use:form>
      <div class="mb-6 grid gap-6 md:grid-cols-2">
        <div>
          <Label for="type" class="mb-2">{$translations.userTypeTitle}</Label>
          <Select id="type" items={userTypeItems} name="type" disabled={selectedUserId === $authenticatedUser?.id} />
          <Helper class="mt-2" color="red">
            {#if $errors.type}
              {$errors.type}
            {/if}
          </Helper>
        </div>
        <div>
          <Label for="ownerId" class="mb-2">{$translations.ownerIdTitle}</Label>
          <Select id="ownerId" items={userItems} name="ownerId" disabled={selectedUserId === $authenticatedUser?.id || $data.type === 'admin'} />
          <Helper class="mt-2" color="red">
            {#if $errors.ownerId}
              {$errors.ownerId}
            {/if}
          </Helper>
        </div>
      </div>
      <div class="mb-6">
        <Label for="email" class="mb-2">{$translations.emailTitle}</Label>
        <Input type="email" id="email" name="email" />
        <Helper class="mt-2" color="red">
          {#if $errors.email}
            {$errors.email}
          {/if}
        </Helper>
      </div>
      <div class="mb-6">
        <Label for="displayName" class="mb-2">{$translations.nameTitle}</Label>
        <Input type="text" id="displayName" name="displayName" required />
        <Helper class="mt-2" color="red">
          {#if $errors.displayName}
            {$errors.displayName}
          {/if}
        </Helper>
      </div>
      <div class="mb-6">
        <Label for="description" class="mb-2">{$translations.descriptionTitle}</Label>
        <Input type="text" id="description" name="description" />
        <Helper class="mt-2" color="red">
          {#if $errors.description}
            {$errors.description}
          {/if}
        </Helper>
      </div>
      <div class="mb-6">
        <Label for="username" class="mb-2">{$translations.usernameTitle}</Label>
        <Input type="text" id="username" name="username" required />
        <Helper class="mt-2" color="red">
          {#if $errors.username}
            {$errors.username}
          {/if}
        </Helper>
      </div>
      <div class="mb-6">
        <Label for="password" class="mb-2">{$translations.passwordTitle}</Label>
        <Input type="password" id="password" name="password" required />
        <Helper class="mt-2" color="red">
          {#if $errors.password}
            {$errors.password}
          {/if}
        </Helper>
      </div>
      <Button type="submit">{$translations.applyChangesTitle}</Button>
    </form>
  </div>
</Modal>
