import { Configuration, Meeting, RootServerApi, Token } from 'bmlt-root-server-client';

class ApiClient extends RootServerApi {
  private authorizationHeader: string | null = null;

  constructor(accessToken: string | null = null) {
    super();
    this.accessToken = accessToken;
    this.configuration = new Configuration({
      basePath: apiBaseUrl,
      accessToken: () => this.authorizationHeader ?? '',
    });
  }

  set accessToken(accessToken: string | null) {
    if (!accessToken) {
      this.authorizationHeader = '';
    } else {
      this.authorizationHeader = `Bearer ${accessToken}`;
    }
  }

  get isLoggedIn(): boolean {
    return Boolean(this.authorizationHeader);
  }
}

class ApiClientWrapper {
  static instance = new ApiClientWrapper();

  private api: ApiClient;

  constructor() {
    this.api = new ApiClient();
  }

  set accessToken(accessToken: string | null) {
    this.api.accessToken = accessToken;
  }

  get isLoggedIn(): boolean {
    return this.api.isLoggedIn;
  }

  async login(username: string, password: string): Promise<Token> {
    const tokenCredentials = { username, password };
    const authTokenRequest = { tokenCredentials };
    return this.api.authToken(authTokenRequest);
  }

  async logout(): Promise<void> {
    return this.api.authLogout();
  }

  async getMeetings(): Promise<Meeting[]> {
    return this.api.getMeetings();
  }
}

export default ApiClientWrapper.instance;
