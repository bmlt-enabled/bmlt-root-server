import { Navigate } from 'react-router-dom';
import RootServerApi from '../RootServerApi';
import { AppLayout } from '../layouts/AppLayout';
import { loadAccessToken } from '../helpers/utils';

type Props = {
  children: JSX.Element;
};

export const ProtectedRoute = ({ children }: Props) => {
  loadAccessToken();
  if (RootServerApi.isLoggedIn) {
    return <AppLayout>{children}</AppLayout>;
  }

  return <Navigate to='/login' />;
};
