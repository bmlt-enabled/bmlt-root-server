import { render, screen, fireEvent, waitFor } from '@testing-library/svelte';
import { describe, test } from 'vitest';
import ServiceBodyForm from '../components/ServiceBodyForm.svelte';
import ServiceBodyModal from '../components/ServiceBodyModal.svelte';
import { translations } from '../stores/localization';
import type { ServiceBody, User } from 'bmlt-root-server-client';
import { allServiceBodies, allUsers, login } from './sharedDataAndMocks';
import userEvent from '@testing-library/user-event';

const serviceBodies: ServiceBody[] = allServiceBodies;

const selectedServiceBody: ServiceBody = allServiceBodies[0];

const users: User[] = allUsers;

describe('ServiceBodyForm Component', () => {
  test('test Ensure form fields are present', async () => {
    render(ServiceBodyForm, { props: { selectedServiceBody, serviceBodies, users } });

    expect(screen.getByLabelText(translations.getString('nameTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('adminTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('serviceBodyTypeTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('parentIdTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('meetingListEditorsTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('emailTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('descriptionTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('websiteUrlTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('helplineTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('worldIdTitle'))).toBeInTheDocument();
  });

  test('test Initial values are correctly set', async () => {
    render(ServiceBodyForm, { props: { selectedServiceBody, serviceBodies, users } });

    expect(screen.getByLabelText(translations.getString('nameTitle'))).toHaveValue(selectedServiceBody.name);
    expect(screen.getByLabelText(translations.getString('adminTitle'))).toHaveValue(selectedServiceBody.adminUserId.toString());
    expect(screen.getByLabelText(translations.getString('serviceBodyTypeTitle'))).toHaveValue(selectedServiceBody.type);
    expect(screen.getByLabelText(translations.getString('parentIdTitle'))).toHaveValue(selectedServiceBody.parentId?.toString());
    expect(screen.getByLabelText(translations.getString('meetingListEditorsTitle'))).toHaveValue(selectedServiceBody.assignedUserIds.map(String).sort());
    expect(screen.getByLabelText(translations.getString('emailTitle'))).toHaveValue(selectedServiceBody.email);
    expect(screen.getByLabelText(translations.getString('descriptionTitle'))).toHaveValue(selectedServiceBody.description);
    expect(screen.getByLabelText(translations.getString('websiteUrlTitle'))).toHaveValue(selectedServiceBody.url);
    expect(screen.getByLabelText(translations.getString('helplineTitle'))).toHaveValue(selectedServiceBody.helpline);
    expect(screen.getByLabelText(translations.getString('worldIdTitle'))).toHaveValue(selectedServiceBody.worldId);
  });

  test('test Apply Changes button should be disabled initially and enabled after changes', async () => {
    render(ServiceBodyForm, { props: { selectedServiceBody, serviceBodies, users } });

    const applyChangesButton = screen.getByText(translations.getString('applyChangesTitle'));
    expect(applyChangesButton).toBeDisabled();

    const nameInput = screen.getByLabelText(translations.getString('nameTitle'));
    await fireEvent.input(nameInput, { target: { value: 'New Name' } });
    expect(applyChangesButton).not.toBeDisabled();
  });

  test('test Add Service Body button should be disabled initially and enabled after changes', async () => {
    render(ServiceBodyForm, { props: { selectedServiceBody: null, serviceBodies, users } });

    const applyChangesButton = screen.getByText(translations.getString('addServiceBody'));
    expect(applyChangesButton).toBeDisabled();

    const nameInput = screen.getByLabelText(translations.getString('nameTitle'));
    await fireEvent.input(nameInput, { target: { value: 'New Name' } });
    expect(applyChangesButton).not.toBeDisabled();
  });

  test('test Validation errors are displayed with invalid data', async () => {
    render(ServiceBodyForm, { props: { selectedServiceBody: null, serviceBodies, users } });

    const nameInput = screen.getByLabelText(translations.getString('nameTitle'));
    await fireEvent.input(nameInput, { target: { value: '' } });

    const emailInput = screen.getByLabelText(translations.getString('emailTitle'));
    await fireEvent.input(emailInput, { target: { value: 'invalid-email' } });

    const addServiceBodyButton = screen.getByText(translations.getString('addServiceBody'));
    await fireEvent.click(addServiceBodyButton);

    await waitFor(() => {
      expect(screen.getByText('name is a required field')).toBeInTheDocument();
      expect(screen.getByText('email must be a valid email')).toBeInTheDocument();
    });
  });

  test('test Fields are disabled for non-admin users', () => {
    login('NorthernZone');
    render(ServiceBodyForm, { props: { selectedServiceBody, serviceBodies, users } });
    expect(screen.getByLabelText(translations.getString('nameTitle'))).toBeDisabled();
    expect(screen.getByLabelText(translations.getString('adminTitle'))).toBeDisabled();
  });

  test('test Confirm modal appears when attempting to close with unsaved changes', async () => {
    await login('NorthernZone');
    render(ServiceBodyModal, { props: { selectedServiceBody, serviceBodies, users, showModal: true } });
    const user = userEvent.setup();
    const description = (await screen.findByLabelText(translations.getString('descriptionTitle'))) as HTMLInputElement;
    await user.type(description, 'My Description');
    await user.click(await screen.findByRole('button', { name: 'Close modal' }));
    expect(screen.getByText(translations.getString('youHaveUnsavedChanges'))).toBeInTheDocument();
  });
});
