import React from 'react';

type Props = {
  userName: string;
  setUserName: React.Dispatch<React.SetStateAction<string>>;
};

export const AppContext = React.createContext<null | Props>(null);
