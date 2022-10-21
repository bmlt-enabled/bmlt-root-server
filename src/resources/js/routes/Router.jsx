import React from 'react';
import { Routes, Route } from 'react-router-dom';
import { Login } from '../pages/Login';
import { ProtectedLoginRoute } from './ProtectedLoginRoute';
import { ProtectedRoute } from './ProtectedRoute';
import { routes } from './routes';

export const Router = () => {
  return (
    <Routes>
      <Route
        path="/login"
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
