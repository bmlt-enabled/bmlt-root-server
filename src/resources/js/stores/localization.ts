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
    adminTitle: 'Admin', // TODO: translate
    allDays: 'All days', // TODO: translate
    anteMeridiem: 'AM',
    applyChangesTitle: 'Änderung anwenden',
    boroughTitle: 'Borough/City Subsection', // TODO: translate
    busLinesTitle: 'Bus Lines', // TODO: translate
    cancel: 'Stornieren',
    chooseDay: 'Tag wählen',
    chooseStartTime: 'Startzeit wählen',
    cityTownTitle: 'Stadt',
    clearFormTitle: 'Clear Form', // TODO: translate
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
    filter: 'Filter', // TODO: translate
    formatId: 'Format ID', // TODO: translate
    formatsTitle: 'Formate',
    formatTypeCodes: [
      // TODO: translate
      { name: 'Attendance by non-addicts (Open, Closed)', value: 'OPEN_OR_CLOSED' },
      { name: 'Common Needs and Restrictions (Mens Meeting, LGTBQ, No Children, etc.)', value: 'COMMON_NEEDS_OR_RESTRICTION' },
      { name: 'Format should be especially prominent (Clean requirement, etc.)', value: 'ALERT' },
      { name: 'Language', value: 'LANGUAGE' },
      { name: 'Location Code (Wheelchair Accessible, Limited Parking, etc.)', value: 'LOCATION' },
      { name: 'Meeting Format (Speaker, Book Study, etc.)', value: 'MEETING_FORMAT' },
      { name: 'None', value: '' }
    ],
    formatTypeTitle: 'Format Type', // TODO: translate
    helplineTitle: 'Helpline',
    homeTitle: 'Startseite',
    hoursTitle: 'Hours', // TODO: translate
    idTitle: 'ID',
    invalidUsernameOrPassword: 'Ungültiger Benutzername oder Passwort.',
    keyIsRequired: 'Schlüssel ist erforderlich',
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
    nawsFormats: [
      { name: '12 Konzepte', value: 'CPT' },
      { name: 'Fremdsprache', value: 'LANG' },
      { name: 'Basic Text', value: 'BT' },
      { name: 'Neuankömmling/Newcomer', value: 'BEG' },
      { name: 'Kerzenlicht', value: 'CAN' },
      { name: 'Kinder willkommen', value: 'CW' },
      { name: 'Geschlossen', value: 'CLOSED' },
      { name: 'Closed Holidays', value: 'CH' }, // TODO translate
      { name: 'Diskussion/Teilen', value: 'DISC' },
      { name: 'Format variatiiert', value: 'VAR' },
      { name: 'Schwul/Lesbisch', value: 'GL' },
      { name: 'Guiding Principles', value: 'GP' },
      { name: 'Thema Faltblätter', value: 'IP' },
      { name: 'Es funktioniert', value: 'IW' },
      { name: 'Nur für Heute', value: 'JFT' },
      { name: 'Literaturmeeting', value: 'LIT' },
      { name: 'Thema Clean Leben', value: 'LC' },
      { name: 'Meditation', value: 'MED' },
      { name: 'Männer', value: 'M' },
      { name: 'Kinder nicht erlaubt', value: 'NC' },
      { name: 'Rauchen verboten', value: 'NS' },
      { name: 'Keine', value: '' },
      { name: 'Offen', value: 'OPEN' },
      { name: 'Frage & Antwort', value: 'QA' },
      { name: 'Eingeschränkter Zutritt', value: 'RA' },
      { name: 'Raucher', value: 'SMOK' },
      { name: 'Sprecher', value: 'SPK' },
      { name: 'Sprecher / Diskussion', value: 'S-D' },
      { name: 'Ein spirituelles Prinzip pro Tag', value: 'SPAD' },
      { name: 'Schritte', value: 'STEP' },
      { name: 'Schritteleitfaden', value: 'SWG' },
      { name: 'Temporarily Closed Facility', value: 'TC' }, // TODO translate
      { name: 'Themenmeeting', value: 'TOP' },
      { name: 'Traditionenmeeting', value: 'TRAD' },
      { name: 'Virtual', value: 'VM' }, // TODO translate
      { name: 'Virtual and In-Person', value: 'HYBR' }, // TODO translate
      { name: 'Rollstuhlzugang', value: 'WCHR' },
      { name: 'Frauen', value: 'W' },
      { name: 'Junge Menschen', value: 'Y' }
    ],
    nawsFormatTitle: 'NAWS Format', // TODO: translate
    neighborhoodTitle: 'Neighborhood', // TODO: translate
    noFormatTranslationsError: 'At least one translation is required.', // TODO: translate
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
    search: 'Search', // TODO: translate
    searchByName: 'Search by name', // TODO: translate
    searchMeetings: 'Search meetings...', // TODO: translate
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
    tabsBasic: 'Basic', // TODO: translate
    tabsLocation: 'Location', // TODO: translate
    tabsOther: 'Other', // TODO: translate
    time: 'Time',
    timeAfternoon: 'Afternoon',
    timeEvening: 'Evening',
    timeMorning: 'Morning',
    timeZoneSelectPlaceholder: 'Choose option (or leave blank to auto-detect from location)', // TODO: translate
    timeZoneTitle: 'Time Zone', // TODO: translate
    trainLinesTitle: 'Train Lines', // TODO: translate
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
    allDays: 'All days',
    anteMeridiem: 'AM',
    applyChangesTitle: 'Apply Changes',
    boroughTitle: 'Borough/City Subsection',
    busLinesTitle: 'Bus Lines',
    cancel: 'Cancel',
    chooseDay: 'Choose day',
    chooseStartTime: 'Choose start time',
    cityTownTitle: 'City/Town',
    clearFormTitle: 'Clear Form',
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
    filter: 'Filter',
    formatId: 'Format ID',
    formatsTitle: 'Formats',
    formatTypeCodes: [
      { name: 'Attendance by non-addicts (Open, Closed)', value: 'OPEN_OR_CLOSED' },
      { name: 'Common Needs and Restrictions (Mens Meeting, LGTBQ, No Children, etc.)', value: 'COMMON_NEEDS_OR_RESTRICTION' },
      { name: 'Format should be especially prominent (Clean requirement, etc.)', value: 'ALERT' },
      { name: 'Language', value: 'LANGUAGE' },
      { name: 'Location Code (Wheelchair Accessible, Limited Parking, etc.)', value: 'LOCATION' },
      { name: 'Meeting Format (Speaker, Book Study, etc.)', value: 'MEETING_FORMAT' },
      { name: 'None', value: '' }
    ],
    formatTypeTitle: 'Format Type',
    helplineTitle: 'Helpline',
    homeTitle: 'Home',
    hoursTitle: 'Hours',
    idTitle: 'ID',
    invalidUsernameOrPassword: 'Invalid username or password.',
    keyIsRequired: 'key is required',
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
    nawsFormats: [
      { name: '12 Concepts', value: 'CPT' },
      { name: 'Alternate Language', value: 'LANG' },
      { name: 'Basic Text', value: 'BT' },
      { name: 'Beginner/Newcomer', value: 'BEG' },
      { name: 'Candlelight', value: 'CAN' },
      { name: 'Children Welcome', value: 'CW' },
      { name: 'Closed', value: 'CLOSED' },
      { name: 'Closed Holidays', value: 'CH' },
      { name: 'Discussion/Participation', value: 'DISC' },
      { name: 'Format Varies', value: 'VAR' },
      { name: 'Gay/Lesbian', value: 'GL' },
      { name: 'Guiding Principles', value: 'GP' },
      { name: 'IP Study', value: 'IP' },
      { name: 'It Works Study', value: 'IW' },
      { name: 'Just For Today Study', value: 'JFT' },
      { name: 'Literature Study', value: 'LIT' },
      { name: 'Living Clean', value: 'LC' },
      { name: 'Meditation', value: 'MED' },
      { name: 'Men', value: 'M' },
      { name: 'No Children', value: 'NC' },
      { name: 'Non-Smoking', value: 'NS' },
      { name: 'None', value: '' },
      { name: 'Open', value: 'OPEN' },
      { name: 'Questions & Answers', value: 'QA' },
      { name: 'Restricted Access', value: 'RA' },
      { name: 'Smoking', value: 'SMOK' },
      { name: 'Speaker', value: 'SPK' },
      { name: 'Speaker/Discussion', value: 'S-D' },
      { name: 'Spiritual Principle a Day', value: 'SPAD' },
      { name: 'Step', value: 'STEP' },
      { name: 'Step Working Guide Study', value: 'SWG' },
      { name: 'Temporarily Closed Facility', value: 'TC' },
      { name: 'Topic', value: 'TOP' },
      { name: 'Tradition', value: 'TRAD' },
      { name: 'Virtual', value: 'VM' },
      { name: 'Virtual and In-Person', value: 'HYBR' },
      { name: 'Wheelchair-Accessible', value: 'WCHR' },
      { name: 'Women', value: 'W' },
      { name: 'Young People', value: 'Y' }
    ],
    nawsFormatTitle: 'NAWS Format',
    neighborhoodTitle: 'Neighborhood',
    noFormatTranslationsError: 'At least one translation is required.',
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
    search: 'Search',
    searchByName: 'Search by name',
    searchMeetings: 'Search meetings...',
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
    tabsBasic: 'Basic',
    tabsLocation: 'Location',
    tabsOther: 'Other',
    time: 'Time',
    timeAfternoon: 'Afternoon',
    timeEvening: 'Evening',
    timeMorning: 'Morning',
    timeZoneSelectPlaceholder: 'Choose option (or leave blank to auto-detect from location)',
    timeZoneTitle: 'Time Zone',
    trainLinesTitle: 'Train Lines',
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
