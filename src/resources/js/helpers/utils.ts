import { Token } from 'bmlt-root-server-client';
import RootServerApi from '../RootServerApi';

export const loadToken = () => {
  const tokenJson = localStorage.getItem('token');
  console.log('load token');
  if (tokenJson) {
    RootServerApi.token = JSON.parse(tokenJson) as Token;
  }
};

export const loadUserId = () => {
  const token = localStorage.getItem('token');
  if (token) {
    const userId = JSON.parse(token).userId;
    return userId;
  }
};
