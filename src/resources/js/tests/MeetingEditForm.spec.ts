import { render, screen, fireEvent, waitFor } from '@testing-library/svelte';
import { describe, test } from 'vitest';
import MeetingEditForm from '../components/MeetingEditForm.svelte';
import { translations } from '../stores/localization';
import type { Format, Meeting, ServiceBody } from 'bmlt-root-server-client';
import { allFormats, allServiceBodies, allMeetings } from './sharedDataAndMocks';

const formats: Format[] = allFormats;

const serviceBodies: ServiceBody[] = allServiceBodies;

const selectedMeeting: Meeting = allMeetings[0];

// dummy functions for props
function onSaved(_: Meeting) {}
function onClosed() {}
function onDeleted(_: Meeting) {}

describe('MeetingEditForm Component', () => {
  test('test Ensure form fields are present', async () => {
    render(MeetingEditForm, { props: { selectedMeeting, serviceBodies, formats, onSaved, onClosed, onDeleted } });

    const deleteButton = screen.getByRole('button', {
      name: `${translations.getString('deleteMeeting')} ${selectedMeeting.id}`
    });
    expect(deleteButton).toBeInTheDocument();

    // Basic fields
    expect(screen.getByLabelText(translations.getString('meetingIsPublishedTitle'))).toBeChecked();
    expect(screen.getByLabelText(translations.getString('nameTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('timeZoneTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('dayTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('startTimeTitle'))).toBeInTheDocument();
    expect(screen.getByText(translations.getString('durationTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('hoursTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('minutesTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('serviceBodyTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('emailTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('worldIdTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('formatsTitle'))).toBeInTheDocument();

    // Location fields
    expect(screen.getByLabelText(translations.getString('venueTypeTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('longitudeTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('latitudeTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('locationTextTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('extraInfoTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('streetTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('neighborhoodTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('boroughTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('cityTownTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('countySubProvinceTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('stateTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('zipCodeTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('nationTitle'))).toBeInTheDocument();

    // Contact fields
    expect(screen.getByLabelText(translations.getString('contact1NameTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('contact1PhoneTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('contact1EmailTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('contact2NameTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('contact2PhoneTitle'))).toBeInTheDocument();
    expect(screen.getByLabelText(translations.getString('contact2EmailTitle'))).toBeInTheDocument();
    settings.customFields.forEach(({ name, displayName }) => {
      expect(screen.getByLabelText(displayName)).toBeInTheDocument();
      expect(screen.getByLabelText(displayName)).toHaveValue(selectedMeeting.customFields ? selectedMeeting.customFields[name] : '');
    });
  });

  test('test Initial values are correctly set', async () => {
    render(MeetingEditForm, { props: { selectedMeeting, serviceBodies, formats } });
    // Basic fields
    expect(screen.getByLabelText(translations.getString('nameTitle'))).toHaveValue(selectedMeeting.name);
    expect(screen.getByLabelText(translations.getString('timeZoneTitle'))).toHaveValue(selectedMeeting.timeZone);
    expect(screen.getByLabelText(translations.getString('dayTitle'))).toHaveValue(selectedMeeting.day.toString());
    expect(screen.getByLabelText(translations.getString('startTimeTitle'))).toHaveValue(selectedMeeting.startTime);
    expect(screen.getByLabelText(translations.getString('hoursTitle'))).toHaveValue(selectedMeeting.duration.split(':')[0]);
    expect(screen.getByLabelText(translations.getString('minutesTitle'))).toHaveValue(selectedMeeting.duration.split(':')[1]);
    expect(screen.getByLabelText(translations.getString('serviceBodyTitle'))).toHaveValue(selectedMeeting.serviceBodyId.toString());
    expect(screen.getByLabelText(translations.getString('emailTitle'))).toHaveValue(selectedMeeting.email);
    expect(screen.getByLabelText(translations.getString('worldIdTitle'))).toHaveValue(selectedMeeting.worldId);
    const formatsSelect = screen.getByLabelText(translations.getString('formatsTitle')) as HTMLSelectElement;
    const selectedValues = Array.from(formatsSelect.selectedOptions).map((option: HTMLOptionElement) => option.value);
    expect(selectedValues.join(',')).toEqual(selectedMeeting.formatIds.join(','));

    // Location fields
    expect(screen.getByLabelText(translations.getString('venueTypeTitle'))).toHaveValue(selectedMeeting.venueType.toString());
    expect(screen.getByLabelText(translations.getString('longitudeTitle'))).toHaveValue(selectedMeeting.longitude.toString());
    expect(screen.getByLabelText(translations.getString('latitudeTitle'))).toHaveValue(selectedMeeting.latitude.toString());
    expect(screen.getByLabelText(translations.getString('locationTextTitle'))).toHaveValue(selectedMeeting.locationText);
    expect(screen.getByLabelText(translations.getString('extraInfoTitle'))).toHaveValue(selectedMeeting.locationInfo);
    expect(screen.getByLabelText(translations.getString('streetTitle'))).toHaveValue(selectedMeeting.locationStreet);
    expect(screen.getByLabelText(translations.getString('neighborhoodTitle'))).toHaveValue(selectedMeeting.locationNeighborhood);
    expect(screen.getByLabelText(translations.getString('boroughTitle'))).toHaveValue(selectedMeeting.locationCitySubsection);
    expect(screen.getByLabelText(translations.getString('cityTownTitle'))).toHaveValue(selectedMeeting.locationMunicipality);
    expect(screen.getByLabelText(translations.getString('countySubProvinceTitle'))).toHaveValue(selectedMeeting.locationSubProvince);
    expect(screen.getByLabelText(translations.getString('stateTitle'))).toHaveValue(selectedMeeting.locationProvince);
    expect(screen.getByLabelText(translations.getString('zipCodeTitle'))).toHaveValue(selectedMeeting.locationPostalCode1);
    expect(screen.getByLabelText(translations.getString('nationTitle'))).toHaveValue(selectedMeeting.locationNation);

    // Contact fields
    expect(screen.getByLabelText(translations.getString('contact1NameTitle'))).toHaveValue(selectedMeeting.contactName1);
    expect(screen.getByLabelText(translations.getString('contact1PhoneTitle'))).toHaveValue(selectedMeeting.contactPhone1);
    expect(screen.getByLabelText(translations.getString('contact1EmailTitle'))).toHaveValue(selectedMeeting.contactEmail1);
    expect(screen.getByLabelText(translations.getString('contact2NameTitle'))).toHaveValue(selectedMeeting.contactName2);
    expect(screen.getByLabelText(translations.getString('contact2PhoneTitle'))).toHaveValue(selectedMeeting.contactPhone2);
    expect(screen.getByLabelText(translations.getString('contact2EmailTitle'))).toHaveValue(selectedMeeting.contactEmail2);
  });

  test('test Ensure tabs are present for existing meetings', async () => {
    render(MeetingEditForm, { props: { selectedMeeting, serviceBodies, formats } });

    const tabs = [translations.getString('tabsBasic'), translations.getString('tabsLocation'), translations.getString('tabsOther'), translations.getString('tabsChanges')];
    await waitFor(() => {
      tabs.forEach((tab) => {
        expect(screen.getByText(tab)).toBeInTheDocument();
      });
    });
  });

  test('test Ensure tabs are present for new meetings', async () => {
    render(MeetingEditForm, { props: { selectedMeeting: null, serviceBodies, formats } });

    const tabs = [translations.getString('tabsBasic'), translations.getString('tabsLocation'), translations.getString('tabsOther')];
    await waitFor(() => {
      tabs.forEach((tab) => {
        expect(screen.getByText(tab)).toBeInTheDocument();
      });
      expect(screen.queryByText(translations.getString('tabsChanges'))).not.toBeInTheDocument();
    });
  });

  test('test Apply Changes button should be disabled initially and enabled after changes', async () => {
    render(MeetingEditForm, { props: { selectedMeeting, serviceBodies, formats } });

    const applyChangesButton = screen.getByText(translations.getString('applyChangesTitle'));
    expect(applyChangesButton).toBeDisabled();

    const nameInput = screen.getByLabelText(translations.getString('nameTitle'));
    await fireEvent.input(nameInput, { target: { value: 'New Name' } });
    expect(applyChangesButton).not.toBeDisabled();
  });

  test('test Add Meeting button should be disabled initially and enabled after changes', async () => {
    render(MeetingEditForm, { props: { selectedMeeting: null, serviceBodies, formats } });

    const applyChangesButton = screen.getByText(translations.getString('addMeeting'));
    expect(applyChangesButton).toBeDisabled();

    const nameInput = screen.getByLabelText(translations.getString('nameTitle'));
    await fireEvent.input(nameInput, { target: { value: 'New Name' } });
    expect(applyChangesButton).not.toBeDisabled();
  });

  test('test Validation errors are displayed with invalid data', async () => {
    render(MeetingEditForm, { props: { selectedMeeting: null, serviceBodies, formats } });

    const nameInput = screen.getByLabelText(translations.getString('nameTitle'));
    await fireEvent.input(nameInput, { target: { value: '' } });

    const emailInput = screen.getByLabelText(translations.getString('emailTitle'));
    await fireEvent.input(emailInput, { target: { value: 'invalid-email' } });

    const addServiceBodyButton = screen.getByText(translations.getString('addMeeting'));
    await fireEvent.click(addServiceBodyButton);

    await waitFor(() => {
      expect(screen.getByText('name is a required field')).toBeInTheDocument();
      expect(screen.getByText('email must be a valid email')).toBeInTheDocument();
    });
  });
});
