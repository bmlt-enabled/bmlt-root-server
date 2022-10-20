import React from "react";
import { Header } from "./sections/Header";

export const Layout = ({ children }) => {
    return (
        <>
            <Header />
            {children}
        </>
    );
};
