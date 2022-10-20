import React from "react";
import { Navigate } from "react-router-dom";

export const ProtectedLoginRoute = ({ children }) => {
    const token = localStorage.getItem("accessToken");
    if (token) {
        return <Navigate to="/" />;
    } else {
        return children;
    }
};
