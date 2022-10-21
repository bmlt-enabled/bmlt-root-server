import { Configuration, RootServerApi } from 'bmlt-root-server-client';

class ApiClient extends RootServerApi {
  constructor(accessToken = null) {
    super();
    this.accessToken = accessToken;
    // eslint-disable-next-line no-undef
    this.configuration = new Configuration({ basePath: baseUrl, accessToken: () => this.authorizationHeader });
  }

  set accessToken(accessToken) {
    if (accessToken === null) {
      this.authorizationHeader = null;
    } else {
      this.authorizationHeader = `Bearer ${accessToken}`;
    }
  }

  get isLoggedIn() {
    return this.authorizationHeader !== null;
  }
}

class ApiClientWrapper {
  constructor() {
    this.api = new ApiClient();
  }

  set accessToken(accessToken) {
    this.api.accessToken = accessToken;
  }

  get isLoggedIn() {
    return this.api.isLoggedIn;
  }

  login(username, password) {
    const tokenCredentials = { username, password };
    const authTokenRequest = { tokenCredentials };
    return this.api.authToken(authTokenRequest);
  }

  logout() {
    return this.api.authLogout();
  }

  getMeetings() {
    return this.api.getMeetings();
  }
}

const instance = new ApiClientWrapper();

export default instance;
