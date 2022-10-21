import { Navigate } from 'react-router-dom';
import { loadAccessToken } from '../helpers/utils';
import RootServerApi from '../RootServerApi';

type Props = {
  children: JSX.Element;
};

export const ProtectedLoginRoute = ({ children }: Props) => {
  loadAccessToken();
  if (RootServerApi.isLoggedIn) {
    return <Navigate to='/' />;
  }
  return <>{children}</>;
};
