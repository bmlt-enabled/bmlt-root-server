import RootServerApi from '../RootServerApi';

export const loadAccessToken = () => {
  const tokenJson = localStorage.getItem('token');
  if (tokenJson) {
    const token = JSON.parse(tokenJson);
    RootServerApi.accessToken = token.accessToken;
  }
};
