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
import CreateContactList from './Pages/ContactList/create.jsx';
import UpdateContactList from './Pages/ContactList/update.jsx';
import DeleteContactList from './Pages/ContactList/delete.jsx';
import Activities from './Pages/Activities/index.jsx';

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
                <Route path="/contact-lists/create" element={<CreateContactList />} />  
                <Route path="/contact-lists/update/:id" element={<UpdateContactList />} />   
                <Route path="/contact-lists/delete/:id" element={<DeleteContactList />} />   
                <Route path="/activities" element={<Activities />} />   
              </Route>
              {/* missing */}
              <Route path="*" element={<Missing />} /> 
            </Route>
          </Routes>
      </AuthProvider>
    </BrowserRouter>
  </React.StrictMode>,
);