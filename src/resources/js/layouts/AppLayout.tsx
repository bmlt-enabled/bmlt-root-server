import React, { useState, useEffect } from 'react';
import { Container } from '@mui/material';
import { Navbar } from '../sections/Navbar';
import { Header } from '../sections/Header';
import { useAppContext } from '../context/AppContext';
import RootServerApi from '../RootServerApi';

type Props = {
  children: React.ReactNode;
};

export const AppLayout = ({ children }: Props) => {
  const appContext = useAppContext();
  const [loading, setLoading] = useState(true);
  const [notFoundMessage, setNotFoundMessage] = useState('');
  const [authenticationMessage, setAuthenticationMessage] = useState('');
  async function fetchUser() {
    setLoading(true);
    if (RootServerApi.isLoggedIn) {
      const userId = RootServerApi?.token?.userId;
      if (userId) {
        try {
          const userData = await RootServerApi.getUser(userId);
          appContext?.setUserName(userData.displayName);
        } catch (error: any) {
          await RootServerApi.handleErrors({
            error,
            handleAuthenticationError: (error) => setAuthenticationMessage(error.message),
            handleNotFoundError: (error) => setNotFoundMessage(error.message),
            handleError: (error) => console.log('other error', error),
          });
        } finally {
          setLoading(false);
        }
      }
    }
  }

  console.log;
  useEffect(() => {
    fetchUser();
  }, []);

  if (loading) {
    return <div>Loading...</div>;
  }

  // not really sure how to handle these errors right now
  // need to address as the UI is built out
  if (notFoundMessage) {
    return <div>{notFoundMessage}</div>;
  }

  if (authenticationMessage) {
    return <div>{authenticationMessage}</div>;
  }

  return (
    <>
      <Header />
      <Navbar />
      <Container maxWidth='lg'>{children}</Container>
    </>
  );
};
