import { ThemeProvider } from '@mui/material/styles';
import React, { ReactElement } from 'react';

import { theme } from '../../theme';

export default (ui: ReactElement): ReactElement => {
  return <ThemeProvider theme={theme}>{ui}</ThemeProvider>;
};
