import LocalizedStrings from 'react-localization';

export const strings = new LocalizedStrings({
  en: {
    loginVerb: 'Log In',
    loginTitle: 'Login',
    usernameTitle: 'Username',
    passwordTitle: 'Password',
    rootServerTitle: 'Root Server',
    dashboardTitle: 'Dashboard',
    meetingsTitle: 'Meetings',
    serviceBodiesTitle: 'Service Bodies',
    usersTitle: 'Users',
    formatsTitle: 'Formats',
    myAccountTitle: 'My Account',
    signOutTitle: 'Sign Out',
    languageSelectTitle: 'Select Language',
    emailTitle: 'Email:',
    userTitle: 'User',
    createNewUserTitle: 'Create New User',
    userIsATitle: 'User Is A:',
    idTitle: 'ID',
    descriptionTitle: 'Description',
  },
  de: {
    passwordTitle: 'Passwort',
    formatsTitle: 'Formate',
    myAccountTitle: 'Mein Account',
    signOutTitle: 'Abmelden',
    emailTitle: 'E-Mailadresse:',
    createNewUserTitle: 'CErstelle einen neuen Benutzer',
    userIsATitle: 'Benutzer ist ein:',
    descriptionTitle: 'Beschreibung:',
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
  },
  es: {
    loginTitle: 'Acceso',
    loginVerb: 'Acceder',
    passwordTitle: 'Contraseña',
    serviceBodiesTitle: 'Cuerpos de servicio',
    formatsTitle: 'Formatos',
    myAccountTitle: 'Mi cuenta',
    signOutTitle: 'Salir',
    emailTitle: 'Correo electrónica:',
    createNewUserTitle: 'Crear un nuevo usuario',
    descriptionTitle: 'Descripción:',
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
  },
  sv: {
    loginVerb: 'Logga in',
    passwordTitle: 'Lösenord',
    myAccountTitle: 'Mitt konto',
    signOutTitle: 'Logga ut',
    emailTitle: 'E-post:',
    createNewUserTitle: 'Skapa en användare',
    userIsATitle: 'Användaren är en:',
    descriptionTitle: 'Beskrivning:',
  },
});

export function getLanguage(): string {
  return strings.getLanguage();
}

export function setLanguage(language: string): void {
  strings.setLanguage(language);
  localStorage.setItem('language', language);
}

export function restoreLanguage(): string {
  const language = localStorage.getItem('language') || settings.defaultLanguage;
  strings.setLanguage(language);
  return language;
}
