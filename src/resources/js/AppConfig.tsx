import { useContext, useMemo } from 'react';

import { AppContext } from './AppContext';
import { setLanguage } from './localization';
import { Router } from './routes/Router';

export const AppConfig = () => {
  const { state } = useContext(AppContext);
  useMemo(() => {
    setLanguage(state.language);
  }, [state.language]);

  return <Router />;
};
