import RegisterButton from '../../Components/Auth/RegisterButton.jsx'
import {useRef, useState, useEffect} from 'react';
import useAuth from '../../Hooks/useAuth.jsx';
import axios from 'axios';
import { NavLink, useNavigate, useLocation } from "react-router";

export default function Register() {
    const {setAuth} = useAuth();
    const nameRef = useRef();
    const navigate = useNavigate();
    const location = useLocation();
    const from = location.state?.from.pathname || "/";
    const apiUrl = import.meta.env.VITE_API_URL;

    const [user, setUser] = useState('');
    const [pwd, setPwd] = useState('');
    const [pwdConf, setPwdConf] = useState('');
    const [name, setName] = useState('');
    const [errMsg, setErrMsg] = useState('');

    useEffect(() => {
        nameRef.current.focus();
    }, [])

    useEffect(() => {
        setErrMsg('');
    }, [user, pwd])

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        try {
            const response = await axios.post(apiUrl + '/register', 
                {
                    name: name,
                    email: user, 
                    password: pwd,
                    password_confirmation: pwdConf
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
        <div className="Register">
                <section>
                    <p className={errMsg ? "errmsg" : "offscreen"}  aria-live="assertive">{errMsg}</p>
                    <h1>Register</h1>
                    <form onSubmit={handleSubmit}>
                        <label htmlFor="name">Name</label>
                        <input 
                            type="text" 
                            id="name"
                            ref={nameRef}
                            autoComplete="off"
                            onChange={(e) =>  setName(e.target.value)}
                            value={name}
                            required
                        />
                        <label htmlFor="username">Username</label>
                        <input 
                            type="text" 
                            id="username"
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
                        <label htmlFor="password_confirmation">Password</label>
                        <input 
                            type="password" 
                            id="password_confirmation"
                            onChange={(e) =>  setPwdConf(e.target.value)}
                            value={pwdConf}
                            required
                        />
                        <RegisterButton />
                        <p>
                            Go to login <br />
                            <span className="line">
                                <NavLink to="/login">Login</NavLink>
                            </span>
                        </p>
                    </form>
                </section>            
        </div>
    );
}