import { ThemeProvider } from '@mui/material/styles';
import { ReactElement } from 'react';

import { theme } from '../../theme';

export const provideTheme = (ui: ReactElement): ReactElement => {
  return <ThemeProvider theme={theme}>{ui}</ThemeProvider>;
};
