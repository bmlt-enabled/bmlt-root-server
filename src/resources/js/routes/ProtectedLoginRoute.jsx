import React, { useEffect } from "react";
import { Navigate } from "react-router-dom";
import { checkAccessToken } from "../helpers/checkAccessToken";

export const ProtectedLoginRoute = ({ children }) => {
  useEffect(() => {
    checkAccessToken();
  }, []);
  const token = localStorage.getItem("accessToken");
    if (token) {
        return <Navigate to="/" />;
    } else {
        return children;
    }
};
