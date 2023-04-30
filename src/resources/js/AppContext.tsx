import { User } from 'bmlt-root-server-client';
import React, { createContext, useReducer } from 'react';

import { restoreLanguage } from './localization';

type Props = {
  children: React.ReactNode;
};

export enum ActionType {
  SET_USER = 'SET_USER',
  SET_LANGUAGE = 'SET_LANGUAGE',
  SET_MODE = 'SET_MODE',
}

type Action = {
  type: ActionType;
  payload: any;
};

type State = {
  user: User | null;
  language: string;
  mode: string;
};

const initialState = {
  user: null,
  language: restoreLanguage(),
  mode: localStorage.getItem('mode') || 'light',
};

export const AppContext = createContext<{ state: State; dispatch: React.Dispatch<Action> }>({
  state: initialState,
  dispatch: () => null,
});

export const AppContextProvider = ({ children }: Props) => {
  const [state, dispatch] = useReducer((state: State, action: Action) => {
    switch (action.type) {
      case ActionType.SET_USER:
        return { ...state, user: action.payload };
      case ActionType.SET_LANGUAGE:
        return { ...state, language: action.payload };
      case ActionType.SET_MODE:
        return { ...state, mode: action.payload };
      default:
        return state;
    }
  }, initialState);

  return <AppContext.Provider value={{ state, dispatch }}>{children}</AppContext.Provider>;
};
