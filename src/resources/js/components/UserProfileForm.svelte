<script lang="ts">
    import { validator } from '@felte/validator-yup';
    import { createForm } from 'felte';
    import { Button, Helper, Input, Label } from 'flowbite-svelte';
    import * as yup from 'yup';

    import { spinner } from '../stores/spinner';
    import RootServerApi from '../lib/RootServerApi';
    import { formIsDirty } from '../lib/utils';
    import { translations } from '../stores/localization';
    import { authenticatedUser } from '../stores/apiCredentials';

    const initialValues = {
        email: $authenticatedUser?.email ?? '',
        password: '',
        description: $authenticatedUser?.description ?? ''
    };

    const { data, errors, form, isDirty } = createForm({
        initialValues: initialValues,
        onSubmit: async (values) => {
            spinner.show();
            if ($authenticatedUser) {
                await RootServerApi.partialUpdateUser($authenticatedUser.id, values);
            }
        },
        onError: async (error) => {
            console.log(error);
            await RootServerApi.handleErrors(error as Error, {
                handleValidationError: (error) => {
                    errors.set({
                        email: (error?.errors?.email ?? []).join(' '),
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
            schema: yup.object({
                email: yup.string().max(255).email(),
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

    $: isDirty.set(formIsDirty(initialValues, $data));
</script>

<form use:form>
    <div class="grid gap-4 md:grid-cols-2">
        <div class="md:col-span-2">
            <Label for="email" class="mb-2">{$translations.emailTitle}</Label>
            <Input type="email" id="email" name="email" />
            <Helper class="mt-2" color="red">
                {#if $errors.email}
                    {$errors.email}
                {/if}
            </Helper>
        </div>
        <div class="md:col-span-2">
            <Label for="description" class="mb-2">{$translations.descriptionTitle}</Label>
            <Input type="text" id="description" name="description" />
            <Helper class="mt-2" color="red">
                {#if $errors.description}
                    {$errors.description}
                {/if}
            </Helper>
        </div>
        <div class="md:col-span-2">
            <Label for="password" class="mb-2">{$translations.passwordTitle}</Label>
            <Input type="password" id="password" name="password" required />
            <Helper class="mt-2" color="red">
                {#if $errors.password}
                    {$errors.password}
                {/if}
            </Helper>
        </div>
        <div class="md:col-span-2">
            <Button type="submit" class="w-full" disabled={!$isDirty} on:click={disableButtonHack}>
                {$translations.applyChangesTitle}
            </Button>
        </div>
    </div>
</form>
