import { get, writable } from 'svelte/store';
import type { Subscriber, Writable, Unsubscriber } from 'svelte/store';

import type { Token, User } from 'bmlt-root-server-client';

import { spinner } from './spinner';
import RootServerApi from '../lib/RootServerApi';

export const authenticatedUser: Writable<User | null> = writable(null);

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
    authenticatedUser.set(null);
    localStorage.removeItem(ACCESS_TOKEN_STORAGE_KEY);
  }

  async set(token: Token): Promise<void> {
    if (token) {
      const expiresInSeconds = this.secondsUntilExpiration(token);

      if (expiresInSeconds) {
        // Token should still be good, so let's give it to the client
        RootServerApi.token = token;
        this.setInternal(token);

        // We make sure this token is actually still valid by retrieving the currently authenticated user
        if (!get(authenticatedUser)) {
          try {
            spinner.show();
            authenticatedUser.set(await RootServerApi.getUser(token.userId));
          } catch {
            this.logout();
            return;
          } finally {
            spinner.hide();
          }
        }

        // Refresh immediately if token has <= 1 hour, otherwise refresh in 5 minutes
        const timeoutSeconds = expiresInSeconds <= 3600 ? 0 : 300;
        this.refreshTokenTimeout = setTimeout(() => this.refreshToken(token), timeoutSeconds * 1000);
        return;
      }
    }

    this.logout();
  }

  private async refreshToken(token: Token) {
    try {
      this.set(await RootServerApi.refreshToken());
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
        spinner.show();
        await RootServerApi.logout();
      } finally {
        this.clearInternal();
        spinner.hide();
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
