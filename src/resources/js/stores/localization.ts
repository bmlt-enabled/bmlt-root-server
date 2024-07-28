import LocalizedStrings from 'localized-strings';

import { writable } from 'svelte/store';
import type { Subscriber, Unsubscriber } from 'svelte/store';

const strings = new LocalizedStrings({
  en: {
    addServiceBody: 'Add Service Body',
    addUser: 'Add User',
    administratorTitle: 'Administrator',
    adminTitle: 'Admin',
    applyChangesTitle: 'Apply Changes',
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
    userTitle: 'User',
    usersTitle: 'Users',
    userTypeTitle: 'User Type',
    websiteUrlTitle: 'Web Site URL',
    welcome: 'Welcome',
    worldIdTitle: 'World Committee Code'
  },
  de: {
    addServiceBody: 'Service-Body hinzufügen',
    addUser: 'Benutzer hinzufügen',
    administratorTitle: 'Administrator/Administratorin', // TOFIX: translate this way, or just Administrator?
    adminTitle: 'Admin', // TOFIX: translate
    applyChangesTitle: 'Änderung anwenden',
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
    userTitle: 'Benutzer',
    usersTitle: 'Benutzer',
    userTypeTitle: 'User Type', // TOFIX: translate
    websiteUrlTitle: 'Web Site URL', // TOFIX: translate
    welcome: 'Willkommen',
    worldIdTitle: 'World Committee Code' // TOFIX: translate
  },
  dk: {
    loginVerb: 'Log Ind',
    passwordTitle: 'Kodeord',
    serviceBodiesTitle: 'Service Enheder',
    formatsTitle: 'Struktur',
    myAccountTitle: 'Min konto',
    signOutTitle: 'Log ud',
    createNewUserTitle: 'Opret En Ny Bruger',
    userIsATitle: 'Bruger Er En:',
    descriptionTitle: 'Beskrivelse:',
    nameTitle: 'Navn',
    ownedByTitle: 'Ejet Af',
    deleteUserTitle: 'Slet Denne Bruger',
    observerTitle: 'Service enhed overvåger',
    deactivatedUserTitle: 'Deaktiveret bruger',
    noUsers: '- None -'
  },
  es: {
    loginTitle: 'Acceso',
    loginVerb: 'Acceder',
    passwordTitle: 'Contraseña',
    serviceBodiesTitle: 'Cuerpos de servicio',
    formatsTitle: 'Formatos',
    myAccountTitle: 'Mi cuenta',
    signOutTitle: 'Salir',
    emailTitle: 'Correo electrónica',
    createNewUserTitle: 'Crear un nuevo usuario',
    descriptionTitle: 'Descripción:',
    nameTitle: 'Nombre',
    ownedByTitle: 'Controlado Por',
    deleteUserTitle: 'Elimine este usuario',
    adminTitle: 'Administrador',
    serviceBodyAdminTitle: 'Administrador de cuerpo de servicio',
    observerTitle: 'Observador de cuerpo de servicio',
    deactivatedUserTitle: 'Usario desactivado', // TODO: check that this translation is correct
    noUsers: '- None -'
  },
  fa: {},
  fr: {
    loginVerb: 'Connexion',
    passwordTitle: 'Mot de passe',
    meetingsTitle: 'Réunions',
    serviceBodiesTitle: 'Composantes de structure de service',
    usersTitle: 'Utilisateurs',
    myAccountTitle: 'Mon Compte',
    signOutTitle: 'Déconnexion',
    createNewUserTitle: 'Créer un nouvel utilisateur',
    userIsATitle: 'Je suis un:',
    nameTitle: 'Nom',
    ownedByTitle: 'Appartient à',
    deleteUserTitle: 'Supprimer cet utilisateur',
    adminTitle: 'Administrateur',
    serviceBodyAdminTitle: "Administrateur d'une structure de service",
    observerTitle: 'Observateur',
    deactivatedUserTitle: 'Utilisateur désactivé', // TODO: check that this translation is correct
    noUsers: '- None -'
  },
  it: {
    loginVerb: 'Entra',
    serviceBodiesTitle: 'Strutture di Servizio',
    formatsTitle: 'Formatos',
    myAccountTitle: 'Il mio account',
    signOutTitle: 'Esci',
    createNewUserTitle: 'Crea un nuovo utente',
    userIsATitle: "L'utente è un:",
    descriptionTitle: 'Descrizione:',
    nameTitle: 'Nome',
    deleteUserTitle: 'Cancella questo utente',
    adminTitle: 'Amministratore',
    serviceBodyAdminTitle: 'Amministratore della struttura di servizio',
    observerTitle: 'Osservatore nella struttura di servizio',
    deactivatedUserTitle: 'Utente disattivato', // TODO: check that this translation is correct
    noUsers: '- None -'
  },
  pl: {
    loginVerb: 'Zaloguj się',
    passwordTitle: 'Hasło',
    serviceBodiesTitle: 'Organy Służb',
    usersTitle: 'Użytkownicy',
    formatsTitle: 'Formaty',
    myAccountTitle: 'Moje Konto',
    signOutTitle: 'Wyloguj',
    createNewUserTitle: 'Utwórz nowego użytkownika',
    userIsATitle: 'Użytkownik:',
    descriptionTitle: 'Opis:',
    nameTitle: 'Nazwa',
    ownedByTitle: 'Właściciel',
    deleteUserTitle: 'Usuń tego użytkownika',
    serviceBodyAdminTitle: 'Administrator organu służb',
    observerTitle: 'Obserwator z organu służb',
    deactivatedUserTitle: 'Dezaktywowany użytkownik',
    noUsers: '- None -'
  },
  pt: {
    loginVerb: 'Entrar',
    passwordTitle: 'Senha',
    meetingsTitle: 'Reuniões',
    usersTitle: 'Usuários',
    formatsTitle: 'Formato',
    myAccountTitle: 'Minha Conta',
    signOutTitle: 'SAIR',
    createNewUserTitle: 'Criar um novo usuário',
    userIsATitle: 'O usuário é:',
    descriptionTitle: 'Descrição:',
    nameTitle: 'Nome',
    ownedByTitle: 'Pertence a',
    deleteUserTitle: 'Apagar esse usuário',
    adminTitle: 'Administrador',
    serviceBodyAdminTitle: 'Administrador de corpo de serviço',
    observerTitle: 'Observador',
    deactivatedUserTitle: 'Usuário desativado', // TODO: check that this translation is correct
    noUsers: '- None -'
  },
  ru: {
    loginVerb: 'Логин',
    passwordTitle: 'Пароль',
    serviceBodiesTitle: 'Органы Oбслуживания',
    usersTitle: 'Пользователи',
    formatsTitle: 'Форматы',
    myAccountTitle: 'Мой Aккаунт',
    signOutTitle: 'Выход',
    createNewUserTitle: 'Создать нового пользователя',
    userIsATitle: 'Пользователь это:',
    descriptionTitle: 'Описание:',
    nameTitle: 'Имя',
    ownedByTitle: 'Принадлежит',
    deleteUserTitle: 'Удалить пользователя',
    adminTitle: 'Администратор',
    serviceBodyAdminTitle: 'Администратор орагана обслуживания',
    observerTitle: 'Наблюдатель органа обслуживания',
    deactivatedUserTitle: 'Деактивированный пользователь', // TODO: check that this translation is correct
    noUsers: '- None -'
  },
  sv: {
    loginVerb: 'Logga in',
    passwordTitle: 'Lösenord',
    myAccountTitle: 'Mitt konto',
    signOutTitle: 'Logga ut',
    emailTitle: 'E-post',
    createNewUserTitle: 'Skapa en användare',
    userIsATitle: 'Användaren är en:',
    descriptionTitle: 'Beskrivning:',
    nameTitle: 'Namn',
    deleteUserTitle: 'Kassera denna användare',
    adminTitle: 'Administratör',
    serviceBodyAdminTitle: 'Serviceenhet Administratör',
    observerTitle: 'Serviceenhet övervakare',
    deactivatedUserTitle: 'Avaktiverad användare', // TODO: check that this translation is correct
    noUsers: '- None -'
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

  getTranslationsForLanguage(language: string | null): Record<string, string> {
    // @ts-expect-error library missing type def for getContent() and we trust this lib is prob never changing
    return strings._props[language ?? this.getLanguage()] || {};
  }
}

export const translations = new Translations();
