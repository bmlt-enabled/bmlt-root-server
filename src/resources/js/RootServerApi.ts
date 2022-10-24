import {
  Configuration,
  Format,
  FormatCreate,
  FormatPartialUpdate,
  FormatUpdate,
  Meeting,
  MeetingCreate,
  MeetingPartialUpdate,
  MeetingUpdate,
  RootServerApi,
  ServiceBody,
  ServiceBodyCreate,
  ServiceBodyPartialUpdate,
  ServiceBodyUpdate,
  Token,
  User,
  UserCreate,
  UserPartialUpdate,
  UserUpdate,
  AuthenticationError,
  AuthorizationError,
  ValidationError,
  ResponseError,
} from 'bmlt-root-server-client';

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

  async getMeeting(id: number): Promise<Meeting> {
    const params = { meetingId: id };
    return this.api.getMeeting(params);
  }

  async createMeeting(meeting: MeetingCreate): Promise<Meeting> {
    const params = { meetingCreate: meeting };
    return this.api.createMeeting(params);
  }

  async updateMeeting(id: number, meeting: MeetingUpdate): Promise<void> {
    const params = { meetingId: id, meetingUpdate: meeting };
    return this.api.updateMeeting(params);
  }

  async partialUpdateMeeting(id: number, meeting: MeetingPartialUpdate): Promise<void> {
    const params = { meetingId: id, meetingPartialUpdate: meeting };
    return this.api.patchMeeting(params);
  }

  async deleteMeeting(id: number): Promise<void> {
    const params = { meetingId: id };
    return this.api.deleteMeeting(params);
  }

  async getServiceBodies(): Promise<ServiceBody[]> {
    return this.api.getServiceBodies();
  }

  async getServiceBody(id: number): Promise<ServiceBody> {
    const params = { serviceBodyId: id };
    return this.api.getServiceBody(params);
  }

  async createServiceBody(serviceBody: ServiceBodyCreate): Promise<ServiceBody> {
    const params = { serviceBodyCreate: serviceBody };
    return this.api.createServiceBody(params);
  }

  async updateServiceBody(id: number, serviceBody: ServiceBodyUpdate): Promise<void> {
    const params = { serviceBodyId: id, serviceBodyUpdate: serviceBody };
    return this.api.updateServiceBody(params);
  }

  async partialUpdateServiceBody(id: number, serviceBody: ServiceBodyPartialUpdate): Promise<void> {
    const params = { serviceBodyId: id, serviceBodyPartialUpdate: serviceBody };
    return this.api.patchServiceBody(params);
  }

  async deleteServiceBody(id: number): Promise<void> {
    const params = { serviceBodyId: id };
    return this.api.deleteServiceBody(params);
  }

  async getFormats(): Promise<Format[]> {
    return this.api.getFormats();
  }

  async getFormat(id: number): Promise<Format> {
    const params = { formatId: id };
    return this.api.getFormat(params);
  }

  async createFormat(format: FormatCreate): Promise<Format> {
    const params = { formatCreate: format };
    return this.api.createFormat(params);
  }

  async updateFormat(id: number, format: FormatUpdate): Promise<void> {
    const params = { formatId: id, formatUpdate: format };
    return this.api.updateFormat(params);
  }

  async partialUpdateFormat(id: number, format: FormatPartialUpdate): Promise<void> {
    const params = { formatId: id, formatPartialUpdate: format };
    return this.api.patchFormat(params);
  }

  async deleteFormat(id: number): Promise<void> {
    const params = { formatId: id };
    return this.api.deleteFormat(params);
  }

  async getUsers(): Promise<User[]> {
    return this.api.getUsers();
  }

  async getUser(id: number): Promise<User> {
    const params = { userId: id };
    return this.api.getUser(params);
  }

  async createUser(user: UserCreate): Promise<User> {
    const params = { userCreate: user };
    return this.api.createUser(params);
  }

  async updateUser(id: number, user: UserUpdate): Promise<void> {
    const params = { userId: id, userUpdate: user };
    return this.api.updateUser(params);
  }

  async partialUpdateUser(id: number, user: UserPartialUpdate): Promise<void> {
    const params = { userId: id, userPartialUpdate: user };
    return this.api.partialUpdateUser(params);
  }

  async deleteUser(id: number): Promise<void> {
    const params = { userId: id };
    return this.api.deleteUser(params);
  }

  async handleApiErrors(
    error: Error,
    handleAuthenticationError?: (error: AuthenticationError) => void,
    handleAuthorizationError?: (error: AuthorizationError) => void,
    handleValidationError?: (error: ValidationError) => void,
    handleServerError?: (error: any) => void,
    handleNetworkError?: () => void,
    handleError?: (error: any) => void,
  ): Promise<void> {
    // handle network errors first
    if (error.message === 'Failed to fetch') {
      if (handleNetworkError) {
        return handleNetworkError();
      }

      if (handleError) {
        return handleError(error.message);
      }

      // return showErrorDialog(error.message);
      console.log(error.message);
    }

    // handle api errors
    const responseError = error as ResponseError;
    const response = await responseError.response.json();

    if (handleAuthenticationError && response.status === 401) {
      return handleAuthenticationError(response.body as AuthenticationError);
    }

    if (handleAuthorizationError && response.status === 403) {
      return handleAuthorizationError(response.body as AuthorizationError);
    }

    if (handleValidationError && response.status === 422) {
      return handleValidationError(response.body as ValidationError);
    }

    if (handleServerError && response.status > 499) {
      return handleServerError(response.body);
    }

    if (handleError) {
      return handleError(response.body);
    }

    // return showErrorDialog(response.body);
    console.log(response.body);
  }
}

export default ApiClientWrapper.instance;
