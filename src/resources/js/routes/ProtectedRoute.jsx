import React, { useEffect } from 'react'
import { Navigate } from "react-router-dom";

export const ProtectedRoute = ({children}) => {
  const token = localStorage.getItem('accessToken')
  if (token) {
    return children
  } else {
    return <Navigate to="/login" />
  }
}
