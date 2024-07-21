import LocalizedStrings from 'localized-strings';

import { writable } from 'svelte/store';
import type { Subscriber, Unsubscriber } from 'svelte/store';

const strings = new LocalizedStrings({
  en: {
    administratorTitle: 'Administrator',
    applyChangesTitle: 'Apply Changes',
    addUser: 'Add User',
    dashboardTitle: 'Dashboard',
    deactivatedUserTitle: 'Deactivated User',
    delete: 'Delete',
    deleteUser: 'Delete User',
    editUser: 'Edit User',
    descriptionTitle: 'Description',
    emailTitle: 'Email',
    formatsTitle: 'Formats',
    idTitle: 'ID',
    invalidUsernameOrPassword: 'Invalid username or password.',
    languageSelectTitle: 'Select Language',
    loginTitle: 'Login',
    loginVerb: 'Log In',
    meetingsTitle: 'Meetings',
    myAccountTitle: 'My Account',
    nameTitle: 'Name',
    noUsers: '- None -',
    observerTitle: 'Service Body Observer',
    ownedByTitle: 'Owned By',
    passwordTitle: 'Password',
    rootServerTitle: 'Root Server',
    searchByName: 'Search by name',
    serviceBodiesTitle: 'Service Bodies',
    addServiceBody: 'Add Service Body',
    serviceBodyTypeTitle: 'Service Body Type',
    deleteServiceBody: 'Delete Service Body',
    noServiceBodiesTitle: 'No service bodies found.',
    confirmDeleteServiceBody: 'Are you sure you want to delete this service body?',
    serviceBodyAdminTitle: 'Service Body Administrator',
    serviceBodiesNoParent: 'No Parent (Top-Level)',
    serviceBodiesNoPrimaryAdmin: 'No Primary Admin',
    logout: 'Logout',
    userTypeTitle: 'User Type',
    userTitle: 'User',
    usernameTitle: 'Username',
    usersTitle: 'Users',
    noUsersTitle: 'No users found.',
    homeTitle: 'Home',
    confirmDeleteUser: 'Are you sure you want to delete this user?',
    confirmYesImSure: "Yes, I'm sure.",
    userIsDeactivated: 'User is deactivated.',
    userDeleteConflictError: 'Error: User is assigned to service bodies.',
    serviceBodyDeleteConflictError: 'Error: Service Body either has other service bodies or meetings assigned to it.',
    websiteUrlTitle: 'Web Site URL',
    parentIdTitle: 'Service Body Parent',
    helplineTitle: 'Helpline',
    worldIdTitle: 'World Committee Code',
    adminTitle: 'Admin',
    meetingListEditorsTitle: 'Meeting List Editors'
  },
  de: {
    adminTitle: 'Administrator',
    applyChangesTitle: 'Änderung anwenden',
    createNewUserTitle: 'Erstelle einen neuen Benutzer',
    dashboardTitle: 'Dashboard',
    deactivatedUserTitle: 'Deaktivierter Benutzer',
    deleteUserTitle: 'Lösche diesen Benutzer',
    descriptionTitle: 'Beschreibung',
    emailTitle: 'E-Mailadresse',
    formatsTitle: 'Formate',
    idTitle: 'ID',
    languageSelectTitle: 'Sprache auswählen',
    loginTitle: 'Anmeldung',
    loginVerb: 'Anmelden',
    meetingsTitle: 'Meetings',
    myAccountTitle: 'Mein Account',
    nameTitle: 'Name',
    noUsers: '- Keine -',
    observerTitle: 'Service Body Beobachter',
    ownedByTitle: 'Gehört',
    passwordTitle: 'Passwort',
    rootServerTitle: 'Root Server',
    serviceBodiesTitle: 'Service Bodies',
    serviceBodyAdminTitle: 'Service Body Administrator',
    signOutTitle: 'Abmelden',
    userIsATitle: 'Benutzer ist ein:',
    userTitle: 'Benutzer',
    usernameTitle: 'Benutzername',
    usersTitle: 'Benutzer'
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
}

export const translations = new Translations();
