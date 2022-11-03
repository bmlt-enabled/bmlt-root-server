import React, { useContext } from 'react';

type ContextProps = {
  userName: string;
  setUserName: React.Dispatch<React.SetStateAction<string>>;
};

// lint error if children is used inside ContextProps
type Props = {
  children: React.ReactNode;
};

export const AppContext = React.createContext<null | ContextProps>(null);

export const AppContextProvider = ({ children }: Props) => {
  const [userName, setUserName] = React.useState('');

  return <AppContext.Provider value={{ userName, setUserName }}>{children}</AppContext.Provider>;
};

export const useAppContext = () => {
  const appContext = useContext(AppContext);
  if (!AppContext) {
    throw new Error('useAppContext must be used within a AppContextProvider');
  }
  return appContext;
};
