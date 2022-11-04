import React, { createContext, useReducer } from 'react';
import appReducer from './appReducer';

export const AppContext = createContext<any>({});

type Props = {
  children: React.ReactNode;
};

export const AppContextProvider = ({ children }: Props) => {
  const initialState = {
    displayName: '',
  };

  const [state, dispatch] = useReducer(appReducer, initialState);

  const setDisplayName = (payload: string) => {
    console.log('setDisplayName', payload);
    dispatch({
      type: 'SET_DISPLAY_NAME',
      payload: payload,
    });
  };

  return (
    <AppContext.Provider
      value={{
        displayName: state.displayName,
        setDisplayName,
      }}
    >
      {children}
    </AppContext.Provider>
  );
};
