import { Container } from '@mui/material';
import React, { useContext, useEffect, useState } from 'react';

import { ActionType, AppContext } from '../AppContext';
import RootServerApi from '../RootServerApi';
import { Header } from '../sections/Header';
import { Navbar } from '../sections/Navbar';

type Props = {
  children: React.ReactNode;
};

export const AppLayout = ({ children }: Props) => {
  const { dispatch } = useContext(AppContext);
  const [loading, setLoading] = useState(true);
  const [notFoundMessage, setNotFoundMessage] = useState('');

  useEffect(() => {
    if (!RootServerApi.isLoggedIn) {
      dispatch({ type: ActionType.SET_USER, payload: null });
      return;
    }

    const userId = RootServerApi?.token?.userId;
    if (!userId) {
      dispatch({ type: ActionType.SET_USER, payload: null });
      return;
    }

    setLoading(true);
    RootServerApi.getUser(userId)
      .then((user) => dispatch({ type: ActionType.SET_USER, payload: user }))
      .catch((error) =>
        RootServerApi.handleErrors(error, {
          handleNotFoundError: (error) => setNotFoundMessage(error.message),
        }),
      )
      .finally(() => setLoading(false));
  }, [dispatch]);

  if (loading) {
    return <div>Loading...</div>;
  }

  // not really sure how to handle these errors right now
  // need to address as the UI is built out
  if (notFoundMessage) {
    return <div>{notFoundMessage}</div>;
  }

  return (
    <div>
      <Header />
      <Navbar />
      <Container maxWidth='lg'>{children}</Container>
    </div>
  );
};
