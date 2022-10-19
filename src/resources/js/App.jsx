import React from 'react';
import { createRoot } from 'react-dom/client'
import ApiTest from './ApiTest';

export default function App() {
    return <ApiTest/>;
}

if(document.getElementById('root')){
    createRoot(document.getElementById('root')).render(<App />)
}
