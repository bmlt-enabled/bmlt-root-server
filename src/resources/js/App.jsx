import React from 'react';
import { createRoot } from 'react-dom/client'
import ApiTest from './ApiTest';
import { theme } from "./theme";
import { ThemeProvider } from "@mui/material/styles";
import { CssBaseline } from "@mui/material";


export default function App() {
  return (
    <ThemeProvider theme={theme}>
      <CssBaseline />
      <ApiTest/>
    </ThemeProvider>
  );
}

if(document.getElementById('root')){
    createRoot(document.getElementById('root')).render(<App />)
}
