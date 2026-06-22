import LoginButton from '../../Components/Auth/LoginButton.jsx'
import {useRef, useState, useEffect, useContext} from 'react';
import useAuth from '../../Hooks/useAuth.jsx';
import axios from 'axios';
import { NavLink, useNavigate, useLocation } from "react-router";

export default function Login() {
    const {setAuth} = useAuth();
    const userRef = useRef();
    const errRef = useRef();
    const navigate = useNavigate();
    const location = useLocation();
    const from = location.state?.from.pathname || "/";
    const apiUrl = import.meta.env.VITE_API_URL;

    const [user, setUser] = useState('');
    const [pwd, setPwd] = useState('');
    const [errMsg, setErrMsg] = useState('');

    useEffect(() => {
        userRef.current.focus();
    }, [])

    useEffect(() => {
        setErrMsg('');
    }, [user, pwd])

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        try {
            const response = await axios.post(apiUrl + '/login', 
                {
                    email: user, 
                    password: pwd
                },
                {
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    withCredentials:true
                }
            );

            const accessToken = response?.data?.token;
            // const roles = response?.data?.roles;
            setAuth({user, accessToken});
            setUser('');
            setPwd('');
            navigate(from, {replace: true});
        } catch(err) {
            if(!err?.response) {
                setErrMsg('No Server response')
            } else if (err?.response?.status) {
                setErrMsg(err?.response?.status + ' ' + err?.response?.data?.message);
            }
        }

        
    }

    return (
        <div className="Login">
                <section>
                    <p ref={errRef} className={errMsg ? "errmsg" : "offscreen"}  aria-live="assertive">{errMsg}</p>
                    <h1>Sign In</h1>
                    <form onSubmit={handleSubmit}>
                        <label htmlFor="username">Username</label>
                        <input 
                            type="text" 
                            id="username"
                            ref={userRef}
                            autoComplete="off"
                            onChange={(e) =>  setUser(e.target.value)}
                            value={user}
                            required
                        />
                        <label htmlFor="password">Password</label>
                        <input 
                            type="password" 
                            id="password"
                            onChange={(e) =>  setPwd(e.target.value)}
                            value={pwd}
                            required
                        />
                        <LoginButton />
                        <p>
                            Need an Account? <br />
                            <span className="line">
                                <NavLink to="/register">Register</NavLink>
                            </span>
                        </p>
                    </form>
                </section>            
        </div>
    );
}