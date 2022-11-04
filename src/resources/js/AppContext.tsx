import { User } from 'bmlt-root-server-client';
import React, { createContext, useReducer } from 'react';

type Props = {
  children: React.ReactNode;
};

export enum ActionType {
  SET_USER = 'SET_USER',
}

type Action = {
  type: ActionType;
  payload: any;
};

type State = {
  user: User | null;
};

const initialState = {
  user: null,
};

export const AppContext = createContext<{ state: State; dispatch: React.Dispatch<any> }>({
  state: initialState,
  dispatch: () => null,
});

export const AppContextProvider = ({ children }: Props) => {
  const [state, dispatch] = useReducer((state: State, action: Action) => {
    switch (action.type) {
      case ActionType.SET_USER:
        return { ...state, user: action.payload };

      default:
        return state;
    }
  }, initialState);

  return <AppContext.Provider value={{ state, dispatch }}>{children}</AppContext.Provider>;
};
