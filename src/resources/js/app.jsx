// @refresh reset
import React from 'react';
import './bootstrap';
import { BrowserRouter, Routes, Route } from "react-router";
import ReactDOM from "react-dom/client";
import Login from './Pages/Auth/login.jsx';
import Register from './Pages/Auth/register.jsx';
import Home from './Pages/home.jsx';;
import Layout from './Components/Layout.jsx'
import Missing from './Pages/missing.jsx'
import Unauthorized from './Pages/unauthorized.jsx'
import RequireAuth from './Components/RequireAuth.jsx'; 
import ContactLists from './Pages/ContactList/index.jsx';

import { AuthProvider } from './Context/AuthProvider.jsx'


const root = document.getElementById('app');

ReactDOM.createRoot(root).render(
  <React.StrictMode>
    <BrowserRouter>
      <AuthProvider>        
          <Routes>
            <Route path="/" element={<Layout />}>
              {/* public */}
              <Route path="/login" element={<Login />} />
              <Route path="/register" element={<Register />} />
              <Route path="/unauthorized" element={<Unauthorized />} />
              {/* private */}
              <Route element={<RequireAuth />}> 
                <Route path="/" element={<Home />} />   
                <Route path="/contact-lists" element={<ContactLists />} />   
              </Route>
              {/* missing */}
              <Route path="*" element={<Missing />} /> 
            </Route>
          </Routes>
      </AuthProvider>
    </BrowserRouter>
  </React.StrictMode>,
);