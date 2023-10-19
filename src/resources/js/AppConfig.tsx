import { PaletteMode } from '@mui/material';
import { CssBaseline, ThemeProvider, createTheme } from '@mui/material';
import { deepmerge } from '@mui/utils';
import { useContext, useMemo, useState } from 'react';

import { AppContext } from './AppContext';
import { setLanguage } from './localization';
import { Router } from './routes/Router';
import { getDesignTokens, getThemedComponents } from './theme';

export const AppConfig = () => {
  const { state } = useContext(AppContext);
  useMemo(() => {
    setLanguage(state.language);
  }, [state.language]);

  const [mode, setMode] = useState<PaletteMode>('light');
  useMemo(() => {
    setMode(state.mode as PaletteMode);
  }, [state.mode]);
  const theme = useMemo(() => createTheme(deepmerge(getDesignTokens(mode), getThemedComponents(mode))), [mode]);

  return (
    <ThemeProvider theme={theme}>
      <CssBaseline />
      <Router />
    </ThemeProvider>
  );
};
