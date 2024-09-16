import LocalizedStrings from 'localized-strings';

import { writable } from 'svelte/store';
import type { Subscriber, Unsubscriber } from 'svelte/store';

/*eslint sort-keys: ["error", "asc", {caseSensitive: false}]*/
const strings = new LocalizedStrings({
  de: {
    accountSettingsTitle: 'Account Einstellungen',
    accountTitle: 'Account',
    accountTypeTitle: 'AccountTyp',
    addMeeting: 'Add Meeting', // TOFIX: translate
    addServiceBody: 'Service-Body hinzufügen',
    addUser: 'Benutzer hinzufügen',
    administratorTitle: 'Administrator/Administratorin', // TOFIX: translate this way, or just Administrator?
    adminTitle: 'Admin', // TOFIX: translate,
    anteMeridiem: 'AM',
    applyChangesTitle: 'Änderung anwenden',
    boroughTitle: 'Borough/City Subsection', // TOFIX: translate
    busLineTitle: 'Bus Line', // TOFIX: translate
    cancel: 'Cancel', // TOFIX: translate
    chooseDay: 'Choose day',
    chooseStartTime: 'Choose start time',
    cityTownTitle: 'City/Town', // TOFIX: translate?
    closeWithoutSaving: 'Close without saving', // TOFIX: translate
    commentsTitle: 'Comments', // TOFIX: translate
    confirmDeleteMeeting: 'Are you sure you want to delete this meeting?',
    confirmDeleteServiceBody: 'Bist du sicher, dass du diesen Service-Body löschen möchten?',
    confirmDeleteUser: 'Bist du sicher, dass du diesen Benutzer löschen möchten?',
    confirmYesImSure: 'Ja, ich bin sicher.',
    contact1EmailTitle: 'Contact 1 Email',
    contact1NameTitle: 'Contact 1 Name',
    contact1PhoneTitle: 'Contact 1 Phone',
    contact2EmailTitle: 'Contact 2 Email',
    contact2NameTitle: 'Contact 2 Name',
    contact2PhoneTitle: 'Contact 2 Phone',
    countySubProvinceTitle: 'County/Sub-Province', // TOFIX: translate?
    dashboardTitle: 'Dashboard', // TOFIX: translate?
    day: 'Day',
    days_of_week: ['Søndag', 'Mandag', 'Tirsdag', 'Onsdag', 'Torsdag', 'Fredag', 'Lørdag'],
    dayTitle: 'Weekday', // TOFIX: translate?
    deactivatedTitle: 'Deaktiviert',
    deactivatedUserTitle: 'Deaktivierter Benutzer',
    delete: 'löschen',
    deleteMeeting: 'Delete Meeting',
    deleteServiceBody: 'Service-Body löschen',
    deleteUser: 'Lösche diesen Benutzer',
    descriptionTitle: 'Beschreibung',
    durationTitle: 'Duration', // TOFIX: translate?
    editableServiceBodies: 'Service bodies this user can edit (not changeable here)', // TOFIX: translate
    editUser: 'Benutzer bearbeiten',
    emailTitle: 'E-Mail-Adresse',
    extraInfoTitle: 'Extra Info', // TOFIX: translate?
    formatsTitle: 'Formate',
    helplineTitle: 'Helpline',
    homeTitle: 'Startseite',
    idTitle: 'ID',
    invalidUsernameOrPassword: 'Ungültiger Benutzername oder Passwort.',
    languageSelectTitle: 'Sprache auswählen',
    latitudeTitle: 'Latitude', // TOFIX: translate
    loading: 'loading ...', // TOFIX: translate
    locationMapTitle: 'Location Map', // TOFIX: translate
    locationTextTitle: 'Location Text', // TOFIX: translate
    loginTitle: 'Anmeldung',
    loginVerb: 'Anmelden',
    logout: 'Abmelden',
    longitudeTitle: 'Longitude', // TOFIX: translate
    meetingIsPublishedTitle: 'Meeting is Published',
    meetingListEditorsTitle: 'Meeting List Editors', // TOFIX: translate
    meetingsTitle: 'Meetings',
    meetingUnpublishedNote: 'Note: Unpublishing a meeting indicates a temporary closure. If this meeting has closed permanently, please delete it.',
    nameTitle: 'Name',
    nationTitle: 'Nation', // TOFIX: translate
    neighborhoodTitle: 'Neighborhood', // TOFIX: translate
    none: '- Keine -',
    noServiceBodiesTitle: 'No service bodies found.', // TOFIX: translate
    noUsersTitle: 'No users found.', // TOFIX: translate
    observerTitle: 'Service-Body Beobachter',
    ownedByTitle: 'Gehört',
    paginationOf: 'of',
    paginationShowing: 'Showing',
    parentIdTitle: 'Service Body Parent', // TOFIX: translate
    passwordTitle: 'Passwort',
    phoneMeetingTitle: 'Phone Meeting Dial-in Number', // TOFIX: translate
    postMeridiem: 'PM',
    published: 'Published',
    rootServerTitle: 'Root Server',
    searchByName: 'Search by name', // TOFIX: translate
    serverAdministratorTitle: 'Main Server Administrator', // TOFIX: translate
    serviceBodiesNoParent: 'No Parent (Top-Level)', // TOFIX: translate
    serviceBodiesTitle: 'Service Bodies', // TOFIX: translate
    serviceBodyAdminTitle: 'Service Body Administrator', // TOFIX: translate
    serviceBodyDeleteConflictError: 'Error: The service body could not be deleted because it is still associated with meetings or is a parent of other service bodies.', // TOFIX: translate
    serviceBodyTitle: 'Service Body', // TOFIX: translate
    serviceBodyTypeTitle: 'Service Body Type', // TOFIX: translate
    startTimeTitle: 'Start Time', // TOFIX: translate
    stateTitle: 'State/Province', // TOFIX: translate
    streetTitle: 'Street', // TOFIX: translate
    time: 'Time',
    timeAfternoon: 'Afternoon',
    timeEvening: 'Evening',
    timeMorning: 'Morning',
    timeZoneTitle: 'Time Zone', // TOFIX: translate
    trainLineTitle: 'Train Line', // TOFIX: translate
    unpublished: 'Unpublished',
    userDeleteConflictError: 'Error: The user could not be deleted because it is still associated with at least one service body or is the parent of another user.', // TOFIX: translate
    userIsDeactivated: 'User is deactivated.', // TOFIX: translate
    usernameTitle: 'Benutzername',
    usersTitle: 'Benutzer',
    userTitle: 'Benutzer',
    userTypeTitle: 'Benutzertyp',
    venueTypeTitle: 'Venue Type', // TOFIX: translate
    virtualMeetingAdditionalInfoTitle: 'Virtual Meeting Additional Information', // TOFIX: translate
    virtualMeetingTitle: 'Virtual Meeting Link', // TOFIX: translate
    websiteUrlTitle: 'Web Site URL', // TOFIX: translate
    welcome: 'Willkommen',
    worldIdTitle: 'World Committee Code', // TOFIX: translate
    youHaveUnsavedChanges: 'You have unsaved changes. Do you really want to close?', // TOFIX: translate
    zipCodeTitle: 'Zip Code/Postal Code' // TOFIX: translate
  },
  en: {
    accountSettingsTitle: 'Account Settings',
    accountTitle: 'Account',
    accountTypeTitle: 'Account Type',
    addMeeting: 'Add Meeting',
    addServiceBody: 'Add Service Body',
    addUser: 'Add User',
    administratorTitle: 'Administrator',
    adminTitle: 'Admin',
    anteMeridiem: 'AM',
    applyChangesTitle: 'Apply Changes',
    boroughTitle: 'Borough/City Subsection',
    busLineTitle: 'Bus Line',
    cancel: 'Cancel',
    chooseDay: 'Choose day',
    chooseStartTime: 'Choose start time',
    cityTownTitle: 'City/Town',
    closeWithoutSaving: 'Close without saving',
    commentsTitle: 'Comments',
    confirmDeleteMeeting: 'Are you sure you want to delete this meeting?',
    confirmDeleteServiceBody: 'Are you sure you want to delete this service body?',
    confirmDeleteUser: 'Are you sure you want to delete this user?',
    confirmYesImSure: "Yes, I'm sure.",
    contact1EmailTitle: 'Contact 1 Email',
    contact1NameTitle: 'Contact 1 Name',
    contact1PhoneTitle: 'Contact 1 Phone',
    contact2EmailTitle: 'Contact 2 Email',
    contact2NameTitle: 'Contact 2 Name',
    contact2PhoneTitle: 'Contact 2 Phone',
    countySubProvinceTitle: 'County/Sub-Province',
    dashboardTitle: 'Dashboard',
    day: 'Day',
    days_of_week: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
    dayTitle: 'Weekday',
    deactivatedTitle: 'Deactivated',
    deactivatedUserTitle: 'Deactivated User',
    delete: 'Delete',
    deleteMeeting: 'Delete Meeting',
    deleteServiceBody: 'Delete Service Body',
    deleteUser: 'Delete User',
    descriptionTitle: 'Description',
    durationTitle: 'Duration',
    editableServiceBodies: 'Service bodies this user can edit (not changeable here)',
    editUser: 'Edit User',
    emailTitle: 'Email',
    extraInfoTitle: 'Extra Info',
    formatsTitle: 'Formats',
    helplineTitle: 'Helpline',
    homeTitle: 'Home',
    idTitle: 'ID',
    invalidUsernameOrPassword: 'Invalid username or password.',
    languageSelectTitle: 'Select Language',
    latitudeTitle: 'Latitude',
    loading: 'loading ...',
    locationMapTitle: 'Location Map',
    locationTextTitle: 'Location Text',
    loginTitle: 'Login',
    loginVerb: 'Log In',
    logout: 'Logout',
    longitudeTitle: 'Longitude',
    meetingIsPublishedTitle: 'Meeting is Published',
    meetingListEditorsTitle: 'Meeting List Editors',
    meetingsTitle: 'Meetings',
    meetingUnpublishedNote: 'Note: Unpublishing a meeting indicates a temporary closure. If this meeting has closed permanently, please delete it.',
    nameTitle: 'Name',
    nationTitle: 'Nation',
    neighborhoodTitle: 'Neighborhood',
    none: '- None -',
    noServiceBodiesTitle: 'No service bodies found.',
    noUsersTitle: 'No users found.',
    observerTitle: 'Service Body Observer',
    ownedByTitle: 'Owned By',
    paginationOf: 'of',
    paginationShowing: 'Showing',
    parentIdTitle: 'Service Body Parent',
    passwordTitle: 'Password',
    phoneMeetingTitle: 'Phone Meeting Dial-in Number',
    postMeridiem: 'PM',
    published: 'Published',
    rootServerTitle: 'Root Server',
    searchByName: 'Search by name',
    serverAdministratorTitle: 'Main Server Administrator',
    serviceBodiesNoParent: 'No Parent (Top-Level)',
    serviceBodiesTitle: 'Service Bodies',
    serviceBodyAdminTitle: 'Service Body Administrator',
    serviceBodyDeleteConflictError: 'Error: The service body could not be deleted because it is still associated with meetings or is a parent of other service bodies.',
    serviceBodyTitle: 'Service Body',
    serviceBodyTypeTitle: 'Service Body Type',
    startTimeTitle: 'Start Time',
    stateTitle: 'State/Province',
    streetTitle: 'Street',
    time: 'Time',
    timeAfternoon: 'Afternoon',
    timeEvening: 'Evening',
    timeMorning: 'Morning',
    timeZoneTitle: 'Time Zone',
    trainLineTitle: 'Train Line',
    unpublished: 'Unpublished',
    userDeleteConflictError: 'Error: The user could not be deleted because it is still associated with at least one service body or is the parent of another user.',
    userIsDeactivated: 'User is deactivated.',
    usernameTitle: 'Username',
    usersTitle: 'Users',
    userTitle: 'User',
    userTypeTitle: 'User Type',
    venueTypeTitle: 'Venue Type',
    virtualMeetingAdditionalInfoTitle: 'Virtual Meeting Additional Information',
    virtualMeetingTitle: 'Virtual Meeting Link',
    websiteUrlTitle: 'Web Site URL',
    welcome: 'Welcome',
    worldIdTitle: 'World Committee Code',
    youHaveUnsavedChanges: 'You have unsaved changes. Do you really want to close?',
    zipCodeTitle: 'Zip Code/Postal Code'
  }
});

const LANGUAGE_STORAGE_KEY = 'bmltLanguage';

class Translations {
  private store = writable(strings);

  constructor() {
    const language = localStorage.getItem(LANGUAGE_STORAGE_KEY) || settings.defaultLanguage;
    strings.setLanguage(language);
    this.store.set(strings);
  }

  get subscribe(): (run: Subscriber<typeof strings>) => Unsubscriber {
    return this.store.subscribe;
  }

  getLanguage(): string {
    return strings.getLanguage();
  }

  setLanguage(language: string): void {
    strings.setLanguage(language);
    localStorage.setItem(LANGUAGE_STORAGE_KEY, language);
    this.store.set(strings);
  }

  getString(key: string, language?: string): string {
    return strings.getString(key, language ?? this.getLanguage());
  }

  getTranslationsForLanguage(language: string | null = null): Record<string, string> {
    return strings.getContent()[language ?? this.getLanguage()];
  }

  getTranslationsForAllLanguages(): Record<string, Record<string, string>> {
    return strings.getContent();
  }
}

export const translations = new Translations();
