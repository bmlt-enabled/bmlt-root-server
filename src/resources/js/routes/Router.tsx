import { Route, Routes, useNavigate } from 'react-router-dom';

import RootServerApi from '../RootServerApi';
import { Login } from '../pages/Login';
import { ProtectedLoginRoute } from './ProtectedLoginRoute';
import { ProtectedRoute } from './ProtectedRoute';
import { routes } from './routes';

export const Router = () => {
  const navigate = useNavigate();

  RootServerApi.initializeDefaultErrorHandlers({
    handleAuthenticationError: (_) => {
      RootServerApi.token = null;
      navigate('/login');
    },
    handleError: (error) => {
      console.log('TODO: popup dialog for unhandled error');
      console.log(error);
    },
  });

  return (
    <Routes>
      <Route
        path='/login'
        element={
          <ProtectedLoginRoute>
            <Login />
          </ProtectedLoginRoute>
        }
      />
      {routes.map((route) => (
        <Route
          key={route.path}
          path={route.path}
          element={
            <ProtectedRoute>
              <route.component />
            </ProtectedRoute>
          }
        />
      ))}
    </Routes>
  );
};
