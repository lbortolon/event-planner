// @refresh reset
import React from 'react';
import { createRoot } from 'react-dom/client';
import './bootstrap';
import { BrowserRouter, Routes, Route } from "react-router";
import ReactDOM from "react-dom/client";
import Login from './Pages/Auth/login.jsx';
import Test from './Test.jsx';

function App() {
    return (
        <div>
            <h1>Event Planner 2</h1>
        </div>
    );
}

const root = document.getElementById('app');

ReactDOM.createRoot(root).render(
  <React.StrictMode>
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<App />} />
        <Route path="login" element={<Login />} />
      </Routes>
    </BrowserRouter>
  </React.StrictMode>,
);