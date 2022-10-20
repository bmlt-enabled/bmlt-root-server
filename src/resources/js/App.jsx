import React from 'react';
import { createRoot } from 'react-dom/client'
// import ApiTest from './ApiTest';
import { theme } from "./theme";
import { ThemeProvider } from "@mui/material/styles";
import { CssBaseline } from "@mui/material";
// import { AuthController } from './routes/AuthController';
import { BrowserRouter } from "react-router-dom";
import { Router } from './routes/Router';
// import { Layout } from './Layout';

export default function App() {
  return (
    <ThemeProvider theme={theme}>
       <BrowserRouter basename="/main_server/">
        {/* <Layout> */}
          <CssBaseline />
          <Router />
        {/* </Layout> */}
      </BrowserRouter>
    </ThemeProvider>
  );
}

if(document.getElementById('root')){
    createRoot(document.getElementById('root')).render(<App />)
}
