import LocalizedStrings from 'localized-strings';

import { writable } from 'svelte/store';
import type { Subscriber, Unsubscriber } from 'svelte/store';

/*eslint sort-keys: ["error", "asc", {caseSensitive: false}]*/
const strings = new LocalizedStrings({
  de: {
    addServiceBody: 'Service-Body hinzufügen',
    addUser: 'Benutzer hinzufügen',
    administratorTitle: 'Administrator/Administratorin', // TOFIX: translate this way, or just Administrator?
    adminTitle: 'Admin', // TOFIX: translate
    applyChangesTitle: 'Änderung anwenden',
    cancel: 'Cancel', // TOFIX: translate
    closeWithoutSaving: 'Close without saving', // TOFIX: translate
    confirmDeleteServiceBody: 'Bist du sicher, dass du diesen Service-Body löschen möchten?',
    confirmDeleteUser: 'Bist du sicher, dass du diesen Benutzer löschen möchten?',
    confirmYesImSure: 'Ja, ich bin sicher.',
    dashboardTitle: 'Dashboard', // TOFIX: translate?
    deactivatedTitle: 'Deaktiviert',
    deactivatedUserTitle: 'Deaktivierter Benutzer',
    delete: 'löschen',
    deleteServiceBody: 'Service-Body löschen',
    deleteUser: 'Lösche diesen Benutzer',
    descriptionTitle: 'Beschreibung',
    editUser: 'Benutzer bearbeiten',
    emailTitle: 'E-Mail-Adresse',
    formatsTitle: 'Formate',
    helplineTitle: 'Helpline',
    homeTitle: 'Startseite',
    idTitle: 'ID',
    invalidUsernameOrPassword: 'Ungültiger Benutzername oder Passwort.',
    languageSelectTitle: 'Sprache auswählen',
    loginTitle: 'Anmeldung',
    loginVerb: 'Anmelden',
    logout: 'Abmelden',
    meetingListEditorsTitle: 'Meeting List Editors', // TOFIX: translate
    meetingsTitle: 'Meetings',
    myAccountTitle: 'Mein Account',
    nameTitle: 'Name', // TOFIX: translate
    noServiceBodiesTitle: 'No service bodies found.', // TOFIX: translate
    noUsers: '- Keine -',
    noUsersTitle: 'No users found.', // TOFIX: translate
    observerTitle: 'Service-Body Beobachter',
    ownedByTitle: 'Gehört',
    parentIdTitle: 'Service Body Parent', // TOFIX: translate
    passwordTitle: 'Passwort',
    rootServerTitle: 'Root Server',
    searchByName: 'Search by name', // TOFIX: translate
    serviceBodiesNoParent: 'No Parent (Top-Level)', // TOFIX: translate
    serviceBodiesTitle: 'Service Bodies', // TOFIX: translate
    serviceBodyAdminTitle: 'Service Body Administrator', // TOFIX: translate
    serviceBodyDeleteConflictError: 'Error: The service body could not be deleted because it is still associated with meetings or is a parent of other service bodies.', // TOFIX: translate
    serviceBodyTypeTitle: 'Service Body Type', // TOFIX: translate
    userDeleteConflictError: 'Error: The user could not be deleted because it is still associated with at least one service body or is the parent of another user.', // TOFIX: translate
    userIsDeactivated: 'User is deactivated.', // TOFIX: translate
    usernameTitle: 'Benutzername',
    usersTitle: 'Benutzer',
    userTitle: 'Benutzer',
    userTypeTitle: 'User Type', // TOFIX: translate
    websiteUrlTitle: 'Web Site URL', // TOFIX: translate
    welcome: 'Willkommen',
    worldIdTitle: 'World Committee Code', // TOFIX: translate
    youHaveUnsavedChanges: 'You have unsaved changes. Do you really want to close?' // TOFIX: translate
  },
  en: {
    addServiceBody: 'Add Service Body',
    addUser: 'Add User',
    administratorTitle: 'Administrator',
    adminTitle: 'Admin',
    applyChangesTitle: 'Apply Changes',
    cancel: 'Cancel',
    closeWithoutSaving: 'Close without saving',
    confirmDeleteServiceBody: 'Are you sure you want to delete this service body?',
    confirmDeleteUser: 'Are you sure you want to delete this user?',
    confirmYesImSure: "Yes, I'm sure.",
    dashboardTitle: 'Dashboard',
    deactivatedTitle: 'Deactivated',
    deactivatedUserTitle: 'Deactivated User',
    delete: 'Delete',
    deleteServiceBody: 'Delete Service Body',
    deleteUser: 'Delete User',
    descriptionTitle: 'Description',
    editUser: 'Edit User',
    emailTitle: 'Email',
    formatsTitle: 'Formats',
    helplineTitle: 'Helpline',
    homeTitle: 'Home',
    idTitle: 'ID',
    invalidUsernameOrPassword: 'Invalid username or password.',
    languageSelectTitle: 'Select Language',
    loginTitle: 'Login',
    loginVerb: 'Log In',
    logout: 'Logout',
    meetingListEditorsTitle: 'Meeting List Editors',
    meetingsTitle: 'Meetings',
    myAccountTitle: 'My Account',
    nameTitle: 'Name',
    noServiceBodiesTitle: 'No service bodies found.',
    noUsers: '- None -',
    noUsersTitle: 'No users found.',
    observerTitle: 'Service Body Observer',
    ownedByTitle: 'Owned By',
    parentIdTitle: 'Service Body Parent',
    passwordTitle: 'Password',
    rootServerTitle: 'Root Server',
    searchByName: 'Search by name',
    serviceBodiesNoParent: 'No Parent (Top-Level)',
    serviceBodiesTitle: 'Service Bodies',
    serviceBodyAdminTitle: 'Service Body Administrator',
    serviceBodyDeleteConflictError: 'Error: The service body could not be deleted because it is still associated with meetings or is a parent of other service bodies.',
    serviceBodyTypeTitle: 'Service Body Type',
    userDeleteConflictError: 'Error: The user could not be deleted because it is still associated with at least one service body or is the parent of another user.',
    userIsDeactivated: 'User is deactivated.',
    usernameTitle: 'Username',
    usersTitle: 'Users',
    userTitle: 'User',
    userTypeTitle: 'User Type',
    websiteUrlTitle: 'Web Site URL',
    welcome: 'Welcome',
    worldIdTitle: 'World Committee Code',
    youHaveUnsavedChanges: 'You have unsaved changes. Do you really want to close?'
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
