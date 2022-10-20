import React from 'react'
import { Navigate } from "react-router-dom";
import RootServerApi from "../RootServerApi";
import { Layout } from '../Layout';
import { loadAccessToken } from '../helpers/utils';

export const ProtectedRoute = ({children}) => {
  loadAccessToken();
  if (RootServerApi.isLoggedIn) {
    return (
      <Layout>
      {children}
      </Layout>
    )
  }
  
  return <Navigate to="/login" />

}
