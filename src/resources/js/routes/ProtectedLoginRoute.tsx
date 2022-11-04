import { Navigate } from 'react-router-dom';

import RootServerApi from '../RootServerApi';

type Props = {
  children: React.ReactNode;
};

export const ProtectedLoginRoute = ({ children }: Props) => {
  if (RootServerApi.isLoggedIn) {
    return <Navigate to='/' />;
  }

  return <>{children}</>;
};
