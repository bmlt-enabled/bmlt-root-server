import React from 'react';
import { createRoot } from 'react-dom/client';
import { theme } from './theme';
import { ThemeProvider } from '@mui/material/styles';
import { CssBaseline } from '@mui/material';
import { BrowserRouter } from 'react-router-dom';
import { Router } from './routes/Router';
import { AppContextProvider } from './context/AppContext';

export default function App() {
  return (
    <AppContextProvider>
      <ThemeProvider theme={theme}>
        <BrowserRouter basename={apiBaseUrl}>
          <CssBaseline />
          <Router />
        </BrowserRouter>
      </ThemeProvider>
    </AppContextProvider>
  );
}

if (document.getElementById('root')) {
  createRoot(document.getElementById('root') as HTMLElement).render(<App />);
}
