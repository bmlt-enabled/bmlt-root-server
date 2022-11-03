import { Navigate } from 'react-router-dom';
import { loadToken } from '../helpers/utils';
import RootServerApi from '../RootServerApi';

type Props = {
  children: JSX.Element;
};

export const ProtectedLoginRoute = ({ children }: Props) => {
  loadToken();

  if (RootServerApi.isLoggedIn) {
    return <Navigate to='/' />;
  }

  return <>{children}</>;
};
