import { createRoot } from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';

import { AppConfig } from './AppConfig';
import { AppContextProvider } from './AppContext';

export default function App() {
  return (
    <AppContextProvider>
      <BrowserRouter basename={settings.apiBaseUrl}>
        <AppConfig />
      </BrowserRouter>
    </AppContextProvider>
  );
}

if (document.getElementById('root')) {
  createRoot(document.getElementById('root') as HTMLElement).render(<App />);
}
