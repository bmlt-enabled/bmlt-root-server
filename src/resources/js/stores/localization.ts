import LocalizedStrings from 'localized-strings';

import { writable } from 'svelte/store';
import type { Subscriber, Unsubscriber } from 'svelte/store';

/*eslint sort-keys: ["error", "asc", {caseSensitive: false}]*/
const strings = new LocalizedStrings({
  de: {
    accountSettingsTitle: 'Account Einstellungen',
    accountTitle: 'Account',
    accountTypeTitle: 'AccountTyp',
    addFormat: 'Format hinzufügen',
    addMeeting: 'Meeting hinzufügen',
    addServiceBody: 'Service-Body hinzufügen',
    addUser: 'Benutzer hinzufügen',
    administratorTitle: 'Administrator/Administratorin', // TODO: translate this way, or just Administrator?
    adminTitle: 'Admin', // TODO: translate,
    anteMeridiem: 'AM',
    applyChangesTitle: 'Änderung anwenden',
    boroughTitle: 'Borough/City Subsection', // TODO: translate
    busLineTitle: 'Bus Line', // TODO: translate
    cancel: 'Stornieren',
    chooseDay: 'Tag wählen',
    chooseStartTime: 'Startzeit wählen',
    cityTownTitle: 'Stadt',
    closeWithoutSaving: 'Close without saving', // TODO: translate
    commentsTitle: 'Comments', // TODO: translate
    confirmDeleteFormat: 'Are you sure you want to delete this format?', // TODO: translate
    confirmDeleteMeeting: 'Are you sure you want to delete this meeting?', // TODO: translate
    confirmDeleteServiceBody: 'Bist du sicher, dass du diesen Service-Body löschen möchten?',
    confirmDeleteUser: 'Bist du sicher, dass du diesen Benutzer löschen möchten?',
    confirmYesImSure: 'Ja, ich bin sicher.',
    contact1EmailTitle: 'Contact 1 Email',
    contact1NameTitle: 'Contact 1 Name',
    contact1PhoneTitle: 'Contact 1 Phone',
    contact2EmailTitle: 'Contact 2 Email',
    contact2NameTitle: 'Contact 2 Name',
    contact2PhoneTitle: 'Contact 2 Phone',
    countySubProvinceTitle: 'County/Sub-Province', // TODO: translate?
    dashboardTitle: 'Dashboard', // TODO: translate?
    day: 'Day',
    daysOfWeek: ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'],
    dayTitle: 'Wochentag',
    deactivatedTitle: 'Deaktiviert',
    deactivatedUserTitle: 'Deaktivierter Benutzer',
    delete: 'löschen',
    deleteFormat: 'Format löschen',
    deleteMeeting: 'Meeting löschen',
    deleteServiceBody: 'Service-Body löschen',
    deleteUser: 'Lösche diesen Benutzer',
    descriptionTitle: 'Beschreibung',
    durationTitle: 'Duration', // TODO: translate
    editableServiceBodies: 'Service bodies this user can edit (not changeable here)', // TODO: translate
    editFormat: 'Format bearbeiten',
    editUser: 'Benutzer bearbeiten',
    emailTitle: 'E-Mail-Adresse',
    extraInfoTitle: 'Extra Info', // TODO: translate
    formatsTitle: 'Formate',
    helplineTitle: 'Helpline',
    homeTitle: 'Startseite',
    hoursTitle: 'Hours', // TODO: translate
    idTitle: 'ID',
    invalidUsernameOrPassword: 'Ungültiger Benutzername oder Passwort.',
    keyTitle: 'Schlüssel',
    languageSelectTitle: 'Sprache auswählen',
    latitudeTitle: 'Breitengrad',
    loading: 'geladen ...',
    locationMapTitle: 'Location Map', // TOFIX: translate
    locationTextTitle: 'Location Text', // TODO: translate
    loginTitle: 'Anmeldung',
    loginVerb: 'Anmelden',
    logout: 'Abmelden',
    longitudeTitle: 'Längengrad',
    meetingIsPublishedTitle: 'Meeting is Published', // TODO: translate
    meetingListEditorsTitle: 'Meeting List Editors', // TODO: translate
    meetingsTitle: 'Meetings',
    meetingUnpublishedNote: 'Note: Unpublishing a meeting indicates a temporary closure. If this meeting has closed permanently, please delete it.', // TODO: translate
    minutesTitle: 'Minutes', // TODO: translate
    nameTitle: 'Name',
    nationTitle: 'Nation', // TODO: translate
    neighborhoodTitle: 'Neighborhood', // TODO: translate
    noFormatsTitle: 'No formats found.', // TODO: translate
    none: '- Keine -',
    noServiceBodiesTitle: 'No service bodies found.', // TODO: translate
    noTranslationAvailable: 'keine deutsche Version verfügbar',
    noUsersTitle: 'No users found.', // TODO: translate
    noWhitespaceInKey: 'Whitespace not allowed in key', // TODO: translate
    observerTitle: 'Service-Body Beobachter',
    ownedByTitle: 'Gehört',
    paginationOf: 'of',
    paginationShowing: 'Showing',
    parentIdTitle: 'Service Body Parent', // TODO: translate
    passwordTitle: 'Passwort',
    phoneMeetingTitle: 'Phone Meeting Dial-in Number', // TODO: translate
    postMeridiem: 'PM',
    published: 'Published',
    rootServerTitle: 'Root Server',
    searchByName: 'Search by name', // TODO: translate
    serverAdministratorTitle: 'Main Server Administrator', // TODO: translate
    serviceBodiesNoParent: 'No Parent (Top-Level)', // TODO: translate
    serviceBodiesTitle: 'Service Bodies', // TODO: translate
    serviceBodyAdminTitle: 'Service Body Administrator', // TODO: translate
    serviceBodyDeleteConflictError: 'Error: The service body could not be deleted because it is still associated with meetings or is a parent of other service bodies.', // TODO: translate
    serviceBodyTitle: 'Service Body', // TODO: translate
    serviceBodyTypeTitle: 'Service Body Type', // TODO: translate
    showAllTranslations: 'Show all translations', // TODO: translate
    startTimeTitle: 'Start Time', // TODO: translate
    stateTitle: 'State/Province', // TODO: translate
    streetTitle: 'Street', // TODO: translate
    time: 'Time',
    timeAfternoon: 'Afternoon',
    timeEvening: 'Evening',
    timeMorning: 'Morning',
    timeZoneTitle: 'Time Zone', // TODO: translate
    trainLineTitle: 'Train Line', // TODO: translate
    unpublished: 'Unpublished',
    userDeleteConflictError: 'Error: The user could not be deleted because it is still associated with at least one service body or is the parent of another user.', // TODO: translate
    userIsDeactivated: 'User is deactivated.', // TODO: translate
    usernameTitle: 'Benutzername',
    usersTitle: 'Benutzer',
    userTitle: 'Benutzer',
    userTypeTitle: 'Benutzertyp',
    venueTypeTitle: 'Venue Type', // TODO: translate
    virtualMeetingAdditionalInfoTitle: 'Virtual Meeting Additional Information', // TODO: translate
    virtualMeetingTitle: 'Virtual Meeting Link', // TODO: translate
    websiteUrlTitle: 'Web Site URL', // TODO: translate
    welcome: 'Willkommen',
    worldIdTitle: 'World Committee Code', // TODO: translate
    youHaveUnsavedChanges: 'You have unsaved changes. Do you really want to close?', // TODO: translate
    zipCodeTitle: 'Zip Code/Postal Code' // TODO: translate
  },
  en: {
    accountSettingsTitle: 'Account Settings',
    accountTitle: 'Account',
    accountTypeTitle: 'Account Type',
    addFormat: 'Add Format',
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
    confirmDeleteFormat: 'Are you sure you want to delete this format?',
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
    daysOfWeek: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
    dayTitle: 'Weekday',
    deactivatedTitle: 'Deactivated',
    deactivatedUserTitle: 'Deactivated User',
    delete: 'Delete',
    deleteFormat: 'Delete Format',
    deleteMeeting: 'Delete Meeting',
    deleteServiceBody: 'Delete Service Body',
    deleteUser: 'Delete User',
    descriptionTitle: 'Description',
    durationTitle: 'Duration',
    editableServiceBodies: 'Service bodies this user can edit (not changeable here)',
    editFormat: 'Edit Format',
    editUser: 'Edit User',
    emailTitle: 'Email',
    extraInfoTitle: 'Extra Info',
    formatsTitle: 'Formats',
    helplineTitle: 'Helpline',
    homeTitle: 'Home',
    hoursTitle: 'Hours',
    idTitle: 'ID',
    invalidUsernameOrPassword: 'Invalid username or password.',
    keyTitle: 'Key',
    languageSelectTitle: 'Select Language',
    latitudeTitle: 'Latitude',
    loading: 'loading ...',
    locationMapTitle: 'Location Map', // TOFIX: translate
    locationTextTitle: 'Location Text',
    loginTitle: 'Login',
    loginVerb: 'Log In',
    logout: 'Logout',
    longitudeTitle: 'Longitude',
    meetingIsPublishedTitle: 'Meeting is Published',
    meetingListEditorsTitle: 'Meeting List Editors',
    meetingsTitle: 'Meetings',
    meetingUnpublishedNote: 'Note: Unpublishing a meeting indicates a temporary closure. If this meeting has closed permanently, please delete it.',
    minutesTitle: 'Minutes',
    nameTitle: 'Name',
    nationTitle: 'Nation',
    neighborhoodTitle: 'Neighborhood',
    noFormatsTitle: 'No formats found.',
    none: '- None -',
    noServiceBodiesTitle: 'No service bodies found.',
    noTranslationAvailable: 'no English version available',
    noUsersTitle: 'No users found.',
    noWhitespaceInKey: 'Whitespace not allowed in key',
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
    showAllTranslations: 'Show all translations',
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

  getAvailableLanguages(): string[] {
    return strings.getAvailableLanguages();
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
