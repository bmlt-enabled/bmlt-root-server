import React from 'react';
import { Navigate } from 'react-router-dom';
import { loadAccessToken } from '../helpers/utils';
import RootServerApi from '../RootServerApi';

export const ProtectedLoginRoute = ({ children }) => {
  loadAccessToken();
  if (RootServerApi.isLoggedIn) {
    return <Navigate to="/" />;
  }
  return <>{children}</>;
};
