import {Configuration, RootServerApi } from "bmlt-root-server-client/src";

class ApiClient extends RootServerApi {
    constructor(accessToken = null) {
        super();
        this.accessToken = accessToken;
        this.configuration = new Configuration({ basePath: baseUrl, accessToken: () => this._authorizationHeader });
    }

    set accessToken(accessToken) {
        if (accessToken === null) {
            this._authorizationHeader = null;
        } else {
            this._authorizationHeader = 'Bearer ' + accessToken;
        }
    }

    get isLoggedIn() {
        return this._authorizationHeader !== null;
    }
}

class ApiClientWrapper {
    static instance = new ApiClientWrapper();

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
        const tokenCredentials = { username: username, password: password };
        const authTokenRequest = { tokenCredentials: tokenCredentials };
        return this.api.authToken(authTokenRequest);
    }

    logout() {
        return this.api.authLogout();
    }

    getMeetings() {
        return this.api.getMeetings();
    }
}

export default ApiClientWrapper.instance;
