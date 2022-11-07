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
  },
  de: {
    passwordTitle: 'Passwort',
    formatsTitle: 'Formate',
    myAccountTitle: 'Mein Account',
    signOutTitle: 'Abmelden',
  },
  dk: {
    loginVerb: 'Log Ind',
    passwordTitle: 'Kodeord',
    serviceBodiesTitle: 'Service Enheder',
    formatsTitle: 'Struktur',
    myAccountTitle: 'Min konto',
    signOutTitle: 'Log ud',
  },
  es: {
    loginTitle: 'Acceso',
    loginVerb: 'Acceder',
    passwordTitle: 'Contraseña',
    serviceBodiesTitle: 'Cuerpos de servicio',
    formatsTitle: 'Formatos',
    myAccountTitle: 'Mi cuenta',
    signOutTitle: 'Salir',
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
  },
  it: {
    loginVerb: 'Entra',
    serviceBodiesTitle: 'Strutture di Servizio',
    formatsTitle: 'Formatos',
    myAccountTitle: 'Il mio account',
    signOutTitle: 'Esci',
  },
  pl: {
    loginVerb: 'Zaloguj się',
    passwordTitle: 'Hasło',
    serviceBodiesTitle: 'Organy Służb',
    usersTitle: 'Użytkownicy',
    formatsTitle: 'Formaty',
    myAccountTitle: 'Moje Konto',
    signOutTitle: 'Wyloguj',
  },
  pt: {
    loginVerb: 'Entrar',
    passwordTitle: 'Senha',
    meetingsTitle: 'Reuniões',
    usersTitle: 'Usuários',
    formatsTitle: 'Formato',
    myAccountTitle: 'Minha Conta',
    signOutTitle: 'SAIR',
  },
  ru: {
    loginVerb: 'Логин',
    passwordTitle: 'Пароль',
    serviceBodiesTitle: 'Органы Oбслуживания',
    usersTitle: 'Пользователи',
    formatsTitle: 'Форматы',
    myAccountTitle: 'Мой Aккаунт',
    signOutTitle: 'Выход',
  },
  sv: {
    loginVerb: 'Logga in',
    passwordTitle: 'Lösenord',
    myAccountTitle: 'Mitt konto',
    signOutTitle: 'Logga ut',
  },
});

export function getLanguage(): string {
  return strings.getLanguage();
}

export function setLanguage(language: string): void {
  strings.setLanguage(language);
  localStorage.setItem('language', language);
}

export function restoreLanguage(): void {
  const language = localStorage.getItem('language');

  if (language) {
    strings.setLanguage(language);
  }
}
