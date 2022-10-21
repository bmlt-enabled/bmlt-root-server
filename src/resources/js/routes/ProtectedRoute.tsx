import { Navigate } from 'react-router-dom';
import RootServerApi from '../RootServerApi';
import { Layout } from '../Layout';
import { loadAccessToken } from '../helpers/utils';

type Props = {
  children: JSX.Element;
};

export const ProtectedRoute = ({ children }: Props) => {
  loadAccessToken();
  if (RootServerApi.isLoggedIn) {
    return <Layout>{children}</Layout>;
  }

  return <Navigate to='/login' />;
};
