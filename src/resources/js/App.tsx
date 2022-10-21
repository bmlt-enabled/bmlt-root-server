import React from 'react';
import { createRoot } from 'react-dom/client';
import { theme } from './theme';
import { ThemeProvider } from '@mui/material/styles';
import { CssBaseline } from '@mui/material';
import { BrowserRouter } from 'react-router-dom';
import { Router } from './routes/Router';

export default function App() {
  return (
    <ThemeProvider theme={theme}>
      <BrowserRouter basename='/main_server/'>
        <CssBaseline />
        <Router />
      </BrowserRouter>
    </ThemeProvider>
  );
}

if (document.getElementById('root')) {
  createRoot(document.getElementById('root') as HTMLElement).render(<App />);
}
