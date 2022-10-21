import { Container } from '@mui/material';
import React from 'react';
import { Navbar } from './sections/Navbar';
import { Header } from './sections/Header';

export const Layout = ({ children }) => {
  return (
    <>
      <Header />
      <Navbar />
      <Container maxWidth="lg">{children}</Container>
    </>
  );
};
