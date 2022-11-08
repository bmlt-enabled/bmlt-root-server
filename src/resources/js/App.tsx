import { CssBaseline } from '@mui/material';
import { ThemeProvider } from '@mui/material/styles';
import { createRoot } from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';

import { AppConfig } from './AppConfig';
import { AppContextProvider } from './AppContext';
import { theme } from './theme';

export default function App() {
  return (
    <AppContextProvider>
      <ThemeProvider theme={theme}>
        <BrowserRouter basename={apiBaseUrl}>
          <CssBaseline />
          <AppConfig />
        </BrowserRouter>
      </ThemeProvider>
    </AppContextProvider>
  );
}

if (document.getElementById('root')) {
  createRoot(document.getElementById('root') as HTMLElement).render(<App />);
}
