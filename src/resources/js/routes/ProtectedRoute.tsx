import { Navigate } from 'react-router-dom';
import RootServerApi from '../RootServerApi';
import { AppLayout } from '../layouts/AppLayout';

type Props = {
  children: React.ReactNode;
};

export const ProtectedRoute = ({ children }: Props) => {
  if (RootServerApi.isLoggedIn) {
    return <AppLayout>{children}</AppLayout>;
  }

  return <Navigate to='/login' />;
};
