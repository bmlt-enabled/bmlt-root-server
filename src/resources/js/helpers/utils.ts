import RootServerApi from '../RootServerApi';

export const loadAccessToken = () => {
  const tokenJson = localStorage.getItem('token');
  console.log('load token');
  if (tokenJson) {
    const token = JSON.parse(tokenJson);
    RootServerApi.accessToken = token.accessToken;
  }
};

export const loadUserId = () => {
  const token = localStorage.getItem('token');
  if (token) {
    const userId = JSON.parse(token).userId;
    return userId;
  }
};
