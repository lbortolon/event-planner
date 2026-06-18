// @refresh reset
import React from 'react';
import { createRoot } from 'react-dom/client';
// import './bootstrap';

function App() {
    return (
        <div>
            <h1>Event Planner 2</h1>
        </div>
    );
}

const el = document.getElementById('app');
console.log('elemento trovato:', el);

const root = createRoot(el);
root.render(<App />);