import React, { useState } from 'react';
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
  const [notFoundMessage, setNotFoundMessage] = useState('');
  async function fetchUser() {
    if (RootServerApi.isLoggedIn) {
      // get user data
      try {
        const userId = await loadUserId();
        const userData = await RootServerApi.getUser(userId);
        setUserName(userData.displayName);
      } catch (error) {
        console.log(error);
      }
    }
  }

  console.log;
  useEffect(() => {
    fetchUser();
  }, []);

  return (
    <>
      <Header />
      <Navbar />
      <Container maxWidth='lg'>{children}</Container>
    </>
  );
};
