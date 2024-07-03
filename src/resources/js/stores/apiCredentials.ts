import { get, writable } from 'svelte/store';
import type { Subscriber, Writable, Unsubscriber } from 'svelte/store';

import type { Token, User } from 'bmlt-root-server-client';

import RootServerApi from '../lib/RootServerApi';

const ACCESS_TOKEN_STORAGE_KEY = 'bmltToken';

export class ApiCredentialsStore {
  private store: Writable<Token | null>;
  private refreshTokenTimeout: NodeJS.Timeout;

  constructor() {
    this.store = writable(null);
    this.refreshTokenTimeout = setTimeout(() => {}, 2147483647);
    const raw = localStorage.getItem(ACCESS_TOKEN_STORAGE_KEY);
    if (raw) {
      this.set(JSON.parse(raw) as Token);
    }
  }

  private secondsUntilExpiration(token: Token): number {
    const currentTime = Math.floor(Date.now() / 1000);
    const expiresIn = token.expiresAt - currentTime;
    return expiresIn;
  }

  private setInternal(token: Token): void {
    this.store.set(token);
    localStorage.setItem(ACCESS_TOKEN_STORAGE_KEY, JSON.stringify(token));
  }

  private clearInternal(): void {
    clearTimeout(this.refreshTokenTimeout);
    this.store.set(null);
    localStorage.removeItem(ACCESS_TOKEN_STORAGE_KEY);
  }

  set(token: Token): void {
    if (token) {
      const expiresInSeconds = this.secondsUntilExpiration(token);

      if (expiresInSeconds) {
        // Token should still be good, so let's give it to the client
        RootServerApi.token = token;
      }

      if (expiresInSeconds) {
        // Token should still be valid
        this.setInternal(token);

        // Refresh immediately if token has <= 6 minutes, otherwise refresh in 5 minutes
        const timeoutSeconds = expiresInSeconds <= 360 ? 0 : 300;

        this.refreshTokenTimeout = setTimeout(async () => {
          try {
            const newToken = await RootServerApi.refreshToken();
            this.set(newToken);
          } catch (err: any) {
            if (err?.response?.status === 401) {
              // The existing token was not valid... we are logged out.
              this.clearInternal();
              return;
            }

            // If there's at least a minute left on the refreshToken, try again in 5 secs
            if (this.secondsUntilExpiration(token) > 60) {
              setTimeout(() => this.set(token), 5 * 1000);
            } else {
              this.logout();
            }
          }
        }, timeoutSeconds * 1000);

        return;
      }
    }

    this.logout();
  }

  async login(username: string, password: string): Promise<Token> {
    this.clearInternal();
    const token = await RootServerApi.login(username, password);
    this.set(token);
    return token;
  }

  async logout(): Promise<void> {
    const token = get(this.store);
    if (token) {
      try {
        await RootServerApi.logout();
      } finally {
        this.clearInternal();
      }
    }
  }

  get isLoggedIn(): boolean {
    const token = get(this.store);
    return !!token && this.secondsUntilExpiration(token) > 20;
  }

  get subscribe(): (run: Subscriber<Token | null>) => Unsubscriber {
    return this.store.subscribe;
  }

  get userId(): number {
    const token = get(this.store);
    if (token) {
      return token.userId;
    }
    return 0;
  }
}

export const apiCredentials = new ApiCredentialsStore();
export const authenticatedUser: Writable<User | null> = writable(null);

let lastUserId = 0;
apiCredentials.subscribe(async () => {
  if (apiCredentials.isLoggedIn) {
    const userId = apiCredentials.userId;

    if (!userId) {
      lastUserId = 0;
      authenticatedUser.set(null);
      return;
    }

    if (userId === lastUserId) {
      return;
    }

    try {
      const apiUser = await RootServerApi.getUser(userId);
      authenticatedUser.set(apiUser);
    } finally {
      lastUserId = userId;
    }
  } else {
    lastUserId = 0;
    authenticatedUser.set(null);
  }
});
