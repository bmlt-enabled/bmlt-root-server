import React, { useEffect } from 'react'
import { Navigate } from "react-router-dom";
import RootServerApi from '../RootServerApi';
import { checkAccessToken } from "../helpers/checkAccessToken";
import { Layout } from '../Layout';

export const ProtectedRoute = ({children}) => {
  useEffect(() => {
    checkAccessToken();
  }, []);
  const token = localStorage.getItem("accessToken");
  if (token) {
    return (
      <Layout>
      {children}
      </Layout>
    )

  } else {
    return <Navigate to="/login" />
  }
}
