import React from 'react';
import { createRoot } from 'react-dom/client';
import { theme } from './theme';
import { ThemeProvider } from '@mui/material/styles';
import { CssBaseline } from '@mui/material';
import { BrowserRouter } from 'react-router-dom';
import { Router } from './routes/Router';
import { AppContext } from './context/AppContext';

export default function App() {
  const [userName, setUserName] = React.useState('');
  return (
    <AppContext.Provider value={{ userName, setUserName }}>
      <ThemeProvider theme={theme}>
        <BrowserRouter basename='/main_server/'>
          <CssBaseline />
          <Router />
        </BrowserRouter>
      </ThemeProvider>
    </AppContext.Provider>
  );
}

if (document.getElementById('root')) {
  createRoot(document.getElementById('root') as HTMLElement).render(<App />);
}
